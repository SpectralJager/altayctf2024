from piccolo.conf.apps import AppRegistry
from piccolo.engine.postgres import PostgresEngine

DB = PostgresEngine(
    config={
        "database": "relay_coin",
        "user": "relay_coin",
        "password": "relay_coin",
        "host": "relay_coin_db",
        "port": 5432,
    }
)

APP_REGISTRY = AppRegistry(
    apps=["app.piccolo_app"]
)
