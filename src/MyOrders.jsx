import './MyOrders.css';

function MyOrders({ orders = [] }) {
  function getStatus(status) {
    switch (status) {
      case 'pending':
        return 'قيد الانتظار';
      case 'processing':
        return 'قيد التجهيز';
      case 'shipped':
        return 'تم الشحن';
      case 'delivered':
        return 'تم التوصيل';
      case 'cancelled':
        return 'ملغي';
      default:
        return status || 'غير محدد';
    }
  }

  if (orders.length === 0) {
    return (
      <main className="my-orders container">
        <div className="orders-header">
          <h1>طلباتي</h1>
          <p>تابع طلباتك وحالة كل طلب من مكان واحد.</p>
        </div>

        <div className="orders-empty">
          <div className="empty-icon">📦</div>
          <h2>لا توجد طلبات حتى الآن</h2>
          <p>عندما تنشئ طلباً جديداً، سيظهر هنا.</p>
        </div>
      </main>
    );
  }

  return (
    <main className="my-orders container">
      <div className="orders-header">
        <h1>طلباتي</h1>
        <p>تابع طلباتك وحالة كل طلب من مكان واحد.</p>
      </div>

      <div className="orders-list">
        {orders.map((order) => (
          <article className="order-card" key={order.id}>
            <div className="order-top">
              <div>
                <span className="order-label">رقم الطلب</span>
                <h3>#{order.id}</h3>
              </div>

              <span className={`order-status ${order.status}`}>
                {getStatus(order.status)}
              </span>
            </div>

            <div className="order-details">
              <p>
                <strong>تاريخ الطلب:</strong>{' '}
                {order.date || 'غير محدد'}
              </p>

              <p>
                <strong>عدد المنتجات:</strong>{' '}
                {order.items?.reduce(
                  (total, item) => total + item.quantity,
                  0
                ) || 0}
              </p>

              <p>
                <strong>الإجمالي:</strong>{' '}
                ${Number(order.total || 0).toFixed(2)}
              </p>
            </div>

            <div className="order-products">
              {order.items?.map((item, index) => (
                <div className="order-product" key={`${item.id}-${index}`}>
                  <span>{item.name}</span>
                  <span>× {item.quantity}</span>
                </div>
              ))}
            </div>
          </article>
        ))}
      </div>
    </main>
  );
}

export default MyOrders;