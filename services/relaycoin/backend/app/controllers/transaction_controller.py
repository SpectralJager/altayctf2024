from datetime import datetime

from blacksheep import FromJSON, json, Request
from blacksheep.server.controllers import post, get

from app.controllers import BaseController
from app.models import User, Contact, Transaction

class TransactionController(BaseController):
    @post("/send_tokens")
    async def send_tokens(self, request: Request, data: FromJSON[dict]):
        if not hasattr(request, 'user_id'):
            return json({"error": "Не авторизован"}, 401)
        
        receiver_username = data.value.get("receiver")
        amount = float(data.value.get("amount"))
        message = data.value.get("message")
        
        sender = await User.objects().get(User.id == request.user_id)
        receiver = await User.objects().get(User.username == receiver_username)

        if not receiver:
            return json({"error": "Адресат не найден"}, 400)

        if sender.id == receiver.id:
            return json({"error": "Нельзя отправлять себе"}, 400)

        if amount < 1:
            return json({"error": "Минимум 1 токен"}, 400)
        
        if sender.tokens < amount:
            return json({"error": "Недостаточно токенов"}, 400)
        
        sender.tokens -= amount
        receiver.tokens += amount
        await sender.save()
        await receiver.save()
        
        transaction = Transaction(
            sender=sender.masked_username(),
            receiver=receiver.masked_username(),
            amount=amount,
            message=message,
            timestamp=datetime.now().isoformat()
        )
        await transaction.save()

        contact = Contact(user=receiver.id, contact=sender.id)
        await contact.save()
        
        return json({"message": "Токены успешно отправлены"})

    @get("/transactions")
    async def get_transactions(self, request: Request, page: int = 1, page_size: int = None):
        if not hasattr(request, 'user_id'):
            return json({"error": "Не авторизован"}, 401)

        user = await User.objects().get(User.id == request.user_id)
        where = (Transaction.sender == user.masked_username()) | (Transaction.receiver == user.masked_username())

        total_transactions = await Transaction.count().where(where)
        transactions_q = Transaction.select(
            Transaction.id,
            Transaction.sender,
            Transaction.receiver,
            Transaction.amount,
            Transaction.message,
            Transaction.timestamp
        ).where(where).order_by(Transaction.id, ascending=False)

        if page_size is not None:
            transactions_q = transactions_q.offset((page - 1) * page_size).limit(page_size)

        return json({
            "total": total_transactions,
            "page": page,
            "page_size": page_size,
            "transactions": await transactions_q
        })

    @get("/all-transactions")
    async def get_all_transactions(self, page: int = 1, page_size: int = None):
        total_transactions = await Transaction.count()
        transactions_q = Transaction.select(
            Transaction.id,
            Transaction.sender,
            Transaction.receiver,
            Transaction.amount,
            Transaction.timestamp
        ).order_by(Transaction.id, ascending=False)

        if page_size is not None:
            transactions_q = transactions_q.offset((page - 1) * page_size).limit(page_size)

        return json({
            "total": total_transactions,
            "page": page,
            "page_size": page_size,
            "transactions": await transactions_q
        })
