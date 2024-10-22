from typing import Optional

from blacksheep.server.controllers import APIController
from blacksheep.utils import join_fragments

class BaseController(APIController):
    @classmethod
    def route(cls) -> Optional[str]:
        return join_fragments("api")
