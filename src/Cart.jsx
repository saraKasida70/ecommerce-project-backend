
import './Cart.css';
import { useNavigate } from 'react-router-dom';

function Cart({ cart, updateQuantity, removeFromCart }) {
  const navigate = useNavigate();

  const total = cart.reduce(
    (sum, item) => sum + item.price * item.quantity,
    0
  );

  return (
    <div className="container cart-page">
      <h1>سلة المشتريات 🛒</h1>
      <p className="cart-subtitle">
        راجعي منتجاتك قبل إتمام الطلب
      </p>

      {cart.length === 0 ? (
        <div className="cart-empty">
          <span>🛍️</span>
          <h2>السلة فارغة</h2>
          <p>لم تقومي بإضافة أي منتجات بعد.</p>
        </div>
      ) : (
        <>
          <div className="cart-items">
            {cart.map((item) => (
              <div className="cart-item" key={item.id}>
                <img src={item.image} alt={item.name} />

                <div className="cart-item-info">
                  <h3>{item.name}</h3>
                  <p>{item.price} ل.س</p>

                  <div className="quantity-controls">
                    <button
                      onClick={() =>
                        updateQuantity(item.id, item.quantity - 1)
                      }
                      disabled={item.quantity <= 1}
                    >
                      -
                    </button>

                    <span>{item.quantity}</span>

                    <button
                      onClick={() =>
                        updateQuantity(item.id, item.quantity + 1)
                      }
                    >
                      +
                    </button>
                  </div>
                </div>

                <button
                  className="remove-button"
                  onClick={() => removeFromCart(item.id)}
                >
                  حذف
                </button>
              </div>
            ))}
          </div>

          <div className="cart-summary">
            <h2>ملخص الطلب</h2>

            <div className="cart-total">
              <span>المجموع:</span>
              <strong>
                {total.toLocaleString('en-US')} ل.س
              </strong>
            </div>

            <button
              className="checkout-button"
              onClick={() => navigate('/checkout')}
            >
              إتمام الطلب
            </button>
          </div>
        </>
      )}
    </div>
  );
}

export default Cart;
