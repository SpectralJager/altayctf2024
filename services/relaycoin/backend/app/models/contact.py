from piccolo.table import Table
from piccolo.columns import ForeignKey
from .user import User

class Contact(Table):
    user = ForeignKey(User)
    contact = ForeignKey(User)