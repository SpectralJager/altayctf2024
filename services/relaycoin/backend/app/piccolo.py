from blacksheep import Application
from piccolo.engine import engine_finder


async def open_database_connection_pool(application):
    try:
        engine = engine_finder()
        await engine.start_connection_pool()
    except Exception:
        print("Unable to connect to the database")


async def close_database_connection_pool(application):
    try:
        engine = engine_finder()
        await engine.close_connection_pool()
    except Exception:
        print("Unable to connect to the database")


def configure_piccolo(app: Application) -> None:
    app.on_start += open_database_connection_pool
    app.on_stop += close_database_connection_pool
