from piccolo.table import Table
from piccolo.columns import Varchar, Integer, Text
from piccolo.apps.user.tables import BaseUser

class User(BaseUser, Table):
    description = Text()
    tokens = Integer(default=100)
    email = None

    def masked_username(self):
        return f"{self.username[:5]}...{self.username[-5:]}"