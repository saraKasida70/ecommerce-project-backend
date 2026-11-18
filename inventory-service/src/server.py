
from concurrent import futures
import logging

import grpc

import inventory_pb2
import inventory_pb2_grpc

from database import Base, engine, SessionLocal
import models

from inventory_service import InventoryService


class InventoryServicer(inventory_pb2_grpc.InventoryServiceServicer):

    def GetStock(self, request, context):
        if request.product_id <= 0:
            return inventory_pb2.GetStockResponse(
                product_id=request.product_id,
                available_quantity=0,
                success=False,
                message="Product ID must be positive",
            )

        db = SessionLocal()

        try:
            quantity = InventoryService.get_stock(db, request.product_id)

            return inventory_pb2.GetStockResponse(
                product_id=request.product_id,
                available_quantity=quantity,
                success=True,
                message="Stock retrieved successfully",
            )

        except Exception:
            logging.exception("GetStock failed")
            context.abort(
                grpc.StatusCode.INTERNAL,
                "Failed to retrieve stock",
            )

        finally:
            db.close()

    def AddStock(self, request, context):
        db = SessionLocal()

        try:
            success, message, quantity = InventoryService.add_stock(
                db,
                request.product_id,
                request.quantity,
            )

            return inventory_pb2.AddStockResponse(
                success=success,
                message=message,
                product_id=request.product_id,
                available_quantity=quantity,
            )

        except Exception:
            logging.exception("AddStock failed")
            context.abort(
                grpc.StatusCode.INTERNAL,
                "Failed to add stock",
            )

        finally:
            db.close()

    def ReserveStock(self, request, context):
        db = SessionLocal()

        # Do not reserve stock if the client has timed out
        if not context.is_active():
            context.abort(
                grpc.StatusCode.DEADLINE_EXCEEDED,
                "Request deadline exceeded",
            )

        db = SessionLocal()

        try:
            items = [
                type(
                    "StockRequestItem",
                    (),
                    {
                        "product_id": item.product_id,
                        "quantity": item.quantity,
                    },
                )()
                for item in request.items
            ]

            success, message, reservation_id = (
                InventoryService.reserve_stock(
                    db,
                    request.order_reference,
                    items,
                )
            )

            return inventory_pb2.ReserveStockResponse(
                success=success,
                message=message,
                reservation_id=reservation_id,
            )

        except Exception:
            logging.exception("ReserveStock failed")
            context.abort(
                grpc.StatusCode.INTERNAL,
                "Failed to reserve stock",
            )

        finally:
            db.close()

    def ReleaseStock(self, request, context):
        db = SessionLocal()

        try:
            success, message = InventoryService.release_stock(
                db,
                request.reservation_id,
            )

            return inventory_pb2.ReleaseStockResponse(
                success=success,
                message=message,
            )

        except Exception:
            logging.exception("ReleaseStock failed")
            context.abort(
                grpc.StatusCode.INTERNAL,
                "Failed to release stock",
            )

        finally:
            db.close()


def serve():
    Base.metadata.create_all(bind=engine)

    server = grpc.server(
        futures.ThreadPoolExecutor(max_workers=20)
    )

    inventory_pb2_grpc.add_InventoryServiceServicer_to_server(
        InventoryServicer(),
        server,
    )

    server.add_insecure_port("[::]:50051")

    logging.info("Inventory gRPC service starting on port 50051")

    server.start()
    server.wait_for_termination()


if __name__ == "__main__":
    logging.basicConfig(level=logging.INFO)
    serve()
