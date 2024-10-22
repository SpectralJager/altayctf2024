from piccolo.table import Table
from piccolo.columns import Varchar, Integer, Text

class Transaction(Table):
    sender = Varchar()
    receiver = Varchar()
    amount = Integer()
    message = Text()
    timestamp = Varchar()