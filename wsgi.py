"""WSGI entry point for Redway Academy (gunicorn / any WSGI server).

Run locally / on a VPS with:
    gunicorn -w 2 -b 0.0.0.0:8000 wsgi:app

On Hostinger shared hosting, use passenger_wsgi.py instead.
"""
import os

from app import app  # noqa: F401  (the WSGI callable)

if __name__ == "__main__":
    port = int(os.environ.get("PORT", "8000"))
    app.run(host="0.0.0.0", port=port)
