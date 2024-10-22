from blacksheep import FromJSON, Cookie, json, Request
from blacksheep.server.controllers import post, get

from app.controllers import BaseController
from app.models import User
from app.services.auth_service import create_token

class AuthController(BaseController):
    @post("/register")
    async def register(self, data: FromJSON[dict]):
        username = data.value.get("username")
        password = data.value.get("password")
        
        if await User.exists().where(User.username == username):
            return json({"error": "Имя пользователя уже занято"}, 400)
        
        user = User(username=username, tokens=100, password=password)
        await user.save()
        
        token = create_token(user.id)
        response = json({"message": "Пользователь успешно зарегистрирован", "token": token})
        response.set_cookie(Cookie("token", token, max_age=86400, http_only=True))
        return response

    @post("/login")
    async def login(self, data: FromJSON[dict]):
        username = data.value.get("username")
        password = data.value.get("password")
        user_id = await User.login(username, password)

        if user_id:
            token = create_token(user_id)
            response = json({"message": "Успешный вход", "token": token})
            response.set_cookie(Cookie("token", token, max_age=86400, http_only=True))
            return response

        return json({"error": "Неверные учетные данные"}, 401)

    @post("/logout")
    async def logout(self):
        response = json({"message": "Выход выполнен успешно"})
        response.unset_cookie("token")
        return response

    @get("/user")
    async def user(self, request: Request):
        if not hasattr(request, 'user_id'):
            return json(None)

        user = await User.objects().get(User.id == request.user_id)

        return json({
            "username": user.username,
            "balance": user.tokens,
            "description": user.description,
        })