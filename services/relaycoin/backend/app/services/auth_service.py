import jwt
from datetime import datetime, timedelta, UTC
from config import SECRET_KEY

def create_token(user_id: int) -> str:
    payload = {
        'user_id': user_id,
        'exp': datetime.now(UTC) + timedelta(days=1)
    }
    return jwt.encode(payload, SECRET_KEY, algorithm='HS256')

def verify_token(token: str) -> int:
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=['HS256'])
        return payload['user_id']
    except jwt.ExpiredSignatureError:
        return None
    except jwt.InvalidTokenError:
        return None