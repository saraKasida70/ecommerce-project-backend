
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import './Checkout.css';

function Checkout({ cart, setOrders }) {
  const navigate = useNavigate();

  const [form, setForm] = useState({
    name: '',
    phone: '',
    city: '',
    address: '',
    notes: '',
  });

  const [success, setSuccess] = useState(false);
  const [orderId, setOrderId] = useState(null);

  const total = cart.reduce(
    (sum, item) => sum + item.price * item.quantity,
    0
  );

  function handleChange(event) {
    setForm({
      ...form,
      [event.target.name]: event.target.value,
    });
  }

  function handleSubmit(event) {
    event.preventDefault();

    if (cart.length === 0) {
      alert('السلة فارغة!');
      navigate('/cart');
      return;
    }

    // إنشاء رقم جديد للطلب
    const newOrderId = Date.now();

    // إنشاء بيانات الطلب
    const newOrder = {
      id: newOrderId,
      date: new Date().toLocaleString('ar'),
      items: cart.map((item) => ({ ...item })),
      total: total,
      status: 'pending',
      customer: {
        name: form.name,
        phone: form.phone,
        city: form.city,
        address: form.address,
        notes: form.notes,
      },
    };

    // حفظ الطلب في قائمة الطلبات
    setOrders((currentOrders) => [
      ...currentOrders,
      newOrder,
    ]);

    setOrderId(newOrderId);
    setSuccess(true);
  }

  if (success) {
    return (
      <div className="container checkout-success">
        <div className="success-icon">✓</div>

        <h1>تم تأكيد طلبك بنجاح!</h1>

        <p>رقم طلبك: #{orderId}</p>

        <p>شكراً لثقتك بمتجرنا ❤️</p>

        <p>سيتم التواصل معك لتأكيد تفاصيل التوصيل.</p>

        <button onClick={() => navigate('/my-orders')}>
          عرض طلباتي
        </button>

        <button onClick={() => navigate('/')}>
          العودة إلى المتجر
        </button>
      </div>
    );
  }

  return (
    <main className="container checkout-page">
      <h1>إتمام الطلب</h1>

      <p className="checkout-subtitle">
        أدخل بياناتك لإتمام عملية الشراء
      </p>

      <div className="checkout-layout">
        <form className="checkout-form" onSubmit={handleSubmit}>
          <h2>بيانات التوصيل</h2>

          <label>الاسم الكامل</label>
          <input
            type="text"
            name="name"
            value={form.name}
            onChange={handleChange}
            placeholder="أدخل اسمك الكامل"
            required
          />

          <label>رقم الهاتف</label>
          <input
            type="tel"
            name="phone"
            value={form.phone}
            onChange={handleChange}
            placeholder="أدخل رقم هاتفك"
            required
          />

          <label>المدينة</label>
          <input
            type="text"
            name="city"
            value={form.city}
            onChange={handleChange}
            placeholder="أدخل المدينة"
            required
          />

          <label>العنوان بالتفصيل</label>
          <textarea
            name="address"
            value={form.address}
            onChange={handleChange}
            placeholder="الشارع والمنطقة ورقم البناء"
            required
          />

          <label>ملاحظات إضافية (اختياري)</label>
          <textarea
            name="notes"
            value={form.notes}
            onChange={handleChange}
            placeholder="أي ملاحظات حول الطلب..."
          />

          <button
            className="confirm-order-button"
            type="submit"
          >
            تأكيد الطلب
          </button>
        </form>

        <section className="checkout-summary">
          <h2>ملخص الطلب</h2>

          {cart.map((item) => (
            <div className="checkout-product" key={item.id}>
              <img src={item.image} alt={item.name} />

              <div>
                <h3>{item.name}</h3>

                <p>الكمية: {item.quantity}</p>

                <strong>
                  ${(item.price * item.quantity).toFixed(2)}
                </strong>
              </div>
            </div>
          ))}

          <div className="checkout-total">
            <span>الإجمالي</span>
            <strong>${total.toFixed(2)}</strong>
          </div>
        </section>
      </div>
    </main>
  );
}

export default Checkout;
