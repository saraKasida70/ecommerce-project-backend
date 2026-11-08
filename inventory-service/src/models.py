
from sqlalchemy import (
    Column,
    Integer,
    BigInteger,
    String,
    ForeignKey,
    DateTime,
    CheckConstraint,
)
from sqlalchemy.sql import func

from database import Base


class Inventory(Base):
    __tablename__ = "inventory"

    product_id = Column(BigInteger, primary_key=True, index=True)
    available_quantity = Column(Integer, nullable=False, default=0)

    __table_args__ = (
        CheckConstraint(
            "available_quantity >= 0",
            name="check_available_quantity_non_negative",
        ),
    )


class Reservation(Base):
    __tablename__ = "reservations"

    reservation_id = Column(String(36), primary_key=True)
    order_reference = Column(String(100), nullable=False, unique=True)
    status = Column(String(20), nullable=False, default="reserved")
    created_at = Column(DateTime(timezone=True), server_default=func.now())


class ReservationItem(Base):
    __tablename__ = "reservation_items"

    id = Column(Integer, primary_key=True, autoincrement=True)
    reservation_id = Column(
        String(36),
        ForeignKey("reservations.reservation_id", ondelete="CASCADE"),
        nullable=False,
        index=True,
    )
    product_id = Column(BigInteger, nullable=False)
    quantity = Column(Integer, nullable=False)

    __table_args__ = (
        CheckConstraint("quantity > 0", name="check_reservation_quantity_positive"),
    )
