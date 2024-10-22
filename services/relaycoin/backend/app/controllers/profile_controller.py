from blacksheep import FromJSON, Request, json
from blacksheep.server.controllers import post, get

from app.controllers import BaseController
from app.models import User

class ProfileController(BaseController):
    @post("/profile")
    async def update_profile(self, request: Request, data: FromJSON[dict]):
        if not hasattr(request, 'user_id'):
            return json({"error": "Не авторизован"}, 401)

        description = data.value.get("description")
        await User.update({User.description: description}).where(User.id == request.user_id)

        return json({"message": "Профиль успешно обновлен"})

    @get("/all-users")
    async def get_all_users(self, page: int = 1, page_size: int = None):
        users_q = User.select(
            User.username,
        ).order_by(User.id, ascending=False)

        if page_size is not None:
            users_q = users_q.offset((page - 1) * page_size).limit(page_size)

        total_users = await User.count()

        return json({
            "total": total_users,
            "users": await users_q
        })
