
import uuid

from sqlalchemy import select
from sqlalchemy.orm import Session

from models import Inventory, Reservation, ReservationItem


class InventoryService:

    @staticmethod
    def get_stock(db: Session, product_id: int):
        item = db.get(Inventory, product_id)

        if item is None:
            return 0

        return item.available_quantity

    @staticmethod
    def add_stock(db: Session, product_id: int, quantity: int):
        if product_id <= 0:
            return False, "Product ID must be positive", 0

        if quantity <= 0:
            return False, "Quantity must be positive", 0

        try:
            with db.begin():
                stock = db.execute(
                    select(Inventory)
                    .where(Inventory.product_id == product_id)
                    .with_for_update()
                ).scalar_one_or_none()

                if stock is None:
                    stock = Inventory(
                        product_id=product_id,
                        available_quantity=quantity,
                    )
                    db.add(stock)
                    new_quantity = quantity
                else:
                    stock.available_quantity += quantity
                    new_quantity = stock.available_quantity

            return True, "Stock added successfully", new_quantity

        except Exception:
            db.rollback()
            raise

    @staticmethod
    def reserve_stock(db: Session, order_reference: str, items: list):
        try:
            if not order_reference or not items:
                return False, "Order reference and items are required", ""

            quantities = {}

            for item in items:
                if item.product_id <= 0 or item.quantity <= 0:
                    return False, "Product ID and quantity must be positive", ""

                quantities[item.product_id] = (
                    quantities.get(item.product_id, 0) + item.quantity
                )

            product_ids = sorted(quantities.keys())

            with db.begin():
                existing = db.execute(
                    select(Reservation).where(
                        Reservation.order_reference == order_reference
                    )
                ).scalar_one_or_none()

                if existing:
                    if existing.status == "reserved":
                        return True, "Reservation already exists", existing.reservation_id

                    return False, "This order reference was already used", ""

                stock_rows = db.execute(
                    select(Inventory)
                    .where(Inventory.product_id.in_(product_ids))
                    .order_by(Inventory.product_id)
                    .with_for_update()
                ).scalars().all()

                stock_map = {row.product_id: row for row in stock_rows}

                for product_id, quantity in quantities.items():
                    stock = stock_map.get(product_id)

                    if stock is None:
                        return False, f"Product {product_id} does not exist in inventory", ""

                    if stock.available_quantity < quantity:
                        return False, f"Insufficient stock for product {product_id}", ""

                reservation_id = str(uuid.uuid4())

                reservation = Reservation(
                    reservation_id=reservation_id,
                    order_reference=order_reference,
                    status="reserved",
                )

                db.add(reservation)

                for product_id, quantity in quantities.items():
                    stock = stock_map[product_id]
                    stock.available_quantity -= quantity

                    db.add(
                        ReservationItem(
                            reservation_id=reservation_id,
                            product_id=product_id,
                            quantity=quantity,
                        )
                    )

            return True, "Stock reserved successfully", reservation_id

        except Exception:
            db.rollback()
            raise

    @staticmethod
    def release_stock(db: Session, reservation_id: str):
        try:
            with db.begin():
                reservation = db.execute(
                    select(Reservation)
                    .where(Reservation.reservation_id == reservation_id)
                    .with_for_update()
                ).scalar_one_or_none()

                if reservation is None:
                    return False, "Reservation not found"

                if reservation.status == "released":
                    return True, "Reservation already released"

                reservation_items = db.execute(
                    select(ReservationItem)
                    .where(ReservationItem.reservation_id == reservation_id)
                    .order_by(ReservationItem.product_id)
                ).scalars().all()

                for item in reservation_items:
                    stock = db.execute(
                        select(Inventory)
                        .where(Inventory.product_id == item.product_id)
                        .with_for_update()
                    ).scalar_one_or_none()

                    if stock is None:
                        raise ValueError(
                            f"Inventory record for product {item.product_id} not found"
                        )

                    stock.available_quantity += item.quantity

                reservation.status = "released"

            return True, "Stock released successfully"

        except Exception:
            db.rollback()
            raise
