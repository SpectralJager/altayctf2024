from blacksheep import json, Request, FromJSON
from blacksheep.server.controllers import post, get

from app.controllers import BaseController
from app.models import Contact, User

class ContactController(BaseController):
    @get("/contacts")
    async def get_contacts(self, request: Request):
        if not hasattr(request, 'user_id'):
            return json(None)

        my_contacts = await Contact.select(
            Contact.contact.username,
        ).where(Contact.user == request.user_id)

        added_me = await Contact.select(
            Contact.user.username,
            Contact.user.description
        ).where(Contact.contact == request.user_id)

        my_contacts = [{"username": contact['contact.username']} for contact in my_contacts]
        added_me = [{"username": user['user.username'], "description": user['user.description']} for user in added_me]

        return json({"myContacts": my_contacts, 'addedMe': added_me})

    @post("/contacts/add")
    async def add_contacts(self, request: Request, data: FromJSON[dict]):
        if not hasattr(request, 'user_id'):
            return json({"error": "Не авторизован"}, 401)

        username = data.value.get('username')

        if not username:
            return json({"error": "Не передан пользователь"}, 401)

        user = await User.objects().get(User.id == request.user_id)
        contact = await User.objects().get(User.username == username)

        if not contact or not user:
            return json({"error": "Не передан пользователь"}, 401)

        await Contact(user=user, contact=contact).save()

        return json({"message": "Успешно"})
