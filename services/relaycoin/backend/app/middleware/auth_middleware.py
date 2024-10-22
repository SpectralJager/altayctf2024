from app.services.auth_service import verify_token

async def auth_middleware(request, handler):
    token = request.cookies.get('token')
    if token:
        user_id = verify_token(token)
        if user_id:
            request.user_id = user_id
    return await handler(request)