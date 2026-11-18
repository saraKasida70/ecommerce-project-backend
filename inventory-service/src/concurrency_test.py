import concurrent.futures
import grpc

import inventory_pb2
import inventory_pb2_grpc


TARGET_PRODUCT_ID = 9999
REQUEST_QUANTITY = 7


def reserve_once(order_reference: str):
    channel = grpc.insecure_channel("localhost:50051")
    stub = inventory_pb2_grpc.InventoryServiceStub(channel)

    try:
        response = stub.ReserveStock(
            inventory_pb2.ReserveStockRequest(
                order_reference=order_reference,
                items=[inventory_pb2.StockItem(
                    product_id=TARGET_PRODUCT_ID,
                    quantity=REQUEST_QUANTITY,
                )],
            ),
            timeout=10,
        )

        if response.success:
            print(f"SUCCESS | {order_reference} | reservation_id={response.reservation_id} | message={response.message}")
        else:
            print(f"FAIL | {order_reference} | message={response.message}")

    except grpc.RpcError as exc:
        print(f"RPC_ERROR | {order_reference} | code={exc.code()} | details={exc.details()}")
    except Exception as exc:
        print(f"EXCEPTION | {order_reference} | error={exc}")


def main():
    with concurrent.futures.ThreadPoolExecutor(max_workers=2) as executor:
        futures = [
            executor.submit(reserve_once, f"CONCURRENCY-ORDER-{index:02d}")
            for index in range(1, 3)
        ]

        for future in concurrent.futures.as_completed(futures):
            future.result()


if __name__ == "__main__":
    main()
