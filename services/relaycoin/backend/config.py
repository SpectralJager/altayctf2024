import os
import uuid

SECRET_KEY = os.environ.get('SECRET_KEY', str(uuid.uuid4()))