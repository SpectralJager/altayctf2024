from blacksheep.server import Application

from app import controllers # NoQA
from app.middleware import auth_middleware
from app.piccolo import configure_piccolo


async def before_start(application: Application) -> None:
    application.services.add_instance(application)
    application.services.add_alias("app", Application)

def configure_application() -> Application:
    app = Application()

    app.serve_files("public", fallback_document='index.html')

    app.on_start += before_start

    configure_piccolo(app)

    app.use_cors(
        allow_methods="*",
        allow_origins="*",
        allow_headers="*",
        max_age=300,
    )

    app.middlewares.append(auth_middleware)

    return app
