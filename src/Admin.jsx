
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import './Admin.css';

function Admin({ products, setProducts, orders = [], setOrders }) {
  const navigate = useNavigate();

  const [form, setForm] = useState({
    name: '',
    category: 'إلكترونيات',
    price: '',
    stock: '',
    description: '',
  });

  const [imageFile, setImageFile] = useState(null);
  const [imagePreview, setImagePreview] = useState('');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  function handleChange(event) {
    const { name, value } = event.target;

    setForm((current) => ({
      ...current,
      [name]: value,
    }));
  }

  function handleImageChange(event) {
    const file = event.target.files?.[0];

    if (!file) return;

    if (!file.type.startsWith('image/')) {
      setError('يرجى اختيار ملف صورة صالح.');
      return;
    }

    setError('');
    setImageFile(file);
    setImagePreview(URL.createObjectURL(file));
  }

  function handleSubmit(event) {
    event.preventDefault();

    setMessage('');
    setError('');

    if (
      !form.name.trim() ||
      !form.price ||
      form.stock === '' ||
      !form.description.trim() ||
      !imageFile
    ) {
      setError('يرجى تعبئة جميع الحقول واختيار صورة للمنتج.');
      return;
    }

    const price = Number(form.price);
    const stock = Number(form.stock);

    if (!Number.isFinite(price) || price <= 0) {
      setError('السعر يجب أن يكون رقماً أكبر من صفر.');
      return;
    }

    if (!Number.isInteger(stock) || stock < 0) {
      setError('كمية المخزون يجب أن تكون عدداً صحيحاً غير سالب.');
      return;
    }

    const newProduct = {
      id: Date.now(),
      name: form.name.trim(),
      category: form.category,
      price,
      stock,
      image: imagePreview,
      description: form.description.trim(),
    };

    setProducts((current) => [...current, newProduct]);

    setForm({
      name: '',
      category: 'إلكترونيات',
      price: '',
      stock: '',
      description: '',
    });

    setImageFile(null);
    setImagePreview('');
    event.target.reset();

    setMessage('تمت إضافة المنتج بنجاح.');
  }

  function changeStock(id, amount) {
    setProducts((current) =>
      current.map((product) =>
        product.id === id
          ? {
              ...product,
              stock: Math.max(
                0,
                (product.stock ?? 0) + amount
              ),
            }
          : product
      )
    );
  }

  function deleteProduct(id) {
    const confirmed = window.confirm(
      'هل أنت متأكد من حذف هذا المنتج؟'
    );

    if (!confirmed) return;

    setProducts((current) =>
      current.filter((product) => product.id !== id)
    );
  }

  // تغيير حالة الطلب
  function changeOrderStatus(orderId, newStatus) {
    setOrders((currentOrders) =>
      currentOrders.map((order) =>
        order.id === orderId
          ? { ...order, status: newStatus }
          : order
      )
    );
  }

  function getStatusLabel(status) {
    const statuses = {
      pending: 'قيد الانتظار',
      processing: 'قيد التجهيز',
      shipped: 'تم الشحن',
      delivered: 'تم التوصيل',
      cancelled: 'ملغي',
    };

    return statuses[status] || 'غير محدد';
  }

  return (
    <main className="admin-page">

      <div className="admin-header">
        <div>
          <h1>لوحة إدارة المتجر</h1>
          <p>إدارة المنتجات والمخزون والطلبات</p>
        </div>

        <button
          className="admin-back-button"
          onClick={() => navigate('/')}
        >
          العودة إلى المتجر
        </button>
      </div>

      {/* إضافة منتج */}
      <section className="admin-form-card">
        <h2>إضافة منتج جديد</h2>

        <form onSubmit={handleSubmit}>
          <div className="admin-form-grid">

            <div className="admin-field">
              <label htmlFor="product-name">اسم المنتج</label>
              <input
                id="product-name"
                name="name"
                value={form.name}
                onChange={handleChange}
                placeholder="مثلاً: سماعات لاسلكية"
              />
            </div>

            <div className="admin-field">
              <label htmlFor="product-category">التصنيف</label>
              <select
                id="product-category"
                name="category"
                value={form.category}
                onChange={handleChange}
              >
                <option value="إلكترونيات">إلكترونيات</option>
                <option value="أزياء">أزياء</option>
                <option value="المنزل">المنزل</option>
              </select>
            </div>

            <div className="admin-field">
              <label htmlFor="product-price">السعر بالدولار</label>
              <input
                id="product-price"
                name="price"
                type="number"
                min="0.01"
                step="0.01"
                value={form.price}
                onChange={handleChange}
                placeholder="مثلاً: 35"
              />
            </div>

            <div className="admin-field">
              <label htmlFor="product-stock">كمية المخزون</label>
              <input
                id="product-stock"
                name="stock"
                type="number"
                min="0"
                step="1"
                value={form.stock}
                onChange={handleChange}
                placeholder="مثلاً: 20"
              />
            </div>

            <div className="admin-field admin-full-width">
              <label htmlFor="product-description">وصف المنتج</label>
              <textarea
                id="product-description"
                name="description"
                value={form.description}
                onChange={handleChange}
                placeholder="اكتب وصفاً مختصراً للمنتج..."
                rows="3"
              />
            </div>

            <div className="admin-field admin-full-width">
              <label htmlFor="product-image">صورة المنتج</label>
              <input
                id="product-image"
                type="file"
                accept="image/*"
                onChange={handleImageChange}
              />

              {imagePreview && (
                <img
                  className="admin-image-preview"
                  src={imagePreview}
                  alt="معاينة المنتج"
                />
              )}
            </div>

          </div>

          {error && (
            <p className="admin-error">{error}</p>
          )}

          {message && (
            <p className="admin-success">{message}</p>
          )}

          <button
            className="admin-submit-button"
            type="submit"
          >
            إضافة المنتج
          </button>
        </form>
      </section>

      {/* المنتجات والمخزون */}
      <section className="admin-products-card">
        <div className="admin-products-heading">
          <h2>المنتجات والمخزون</h2>
          <span>عدد المنتجات: {products.length}</span>
        </div>

        {products.length === 0 ? (
          <p className="admin-empty">لا توجد منتجات حالياً.</p>
        ) : (
          <div className="admin-table-wrapper">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>المنتج</th>
                  <th>التصنيف</th>
                  <th>السعر</th>
                  <th>المخزون</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>

              <tbody>
                {products.map((product) => (
                  <tr key={product.id}>
                    <td>
                      <div className="admin-product-name">
                        <img
                          src={product.image}
                          alt={product.name}
                        />
                        <span>{product.name}</span>
                      </div>
                    </td>

                    <td>{product.category}</td>

                    <td>
                      ${Number(product.price).toFixed(2)}
                    </td>

                    <td>
                      <span
                        className={
                          (product.stock ?? 0) === 0
                            ? 'stock-value out'
                            : 'stock-value'
                        }
                      >
                        {product.stock ?? 0}
                      </span>
                    </td>

                    <td>
                      <div className="stock-actions">
                        <button
                          type="button"
                          onClick={() =>
                            changeStock(product.id, 1)
                          }
                          title="زيادة المخزون"
                        >
                          +
                        </button>

                        <button
                          type="button"
                          onClick={() =>
                            changeStock(product.id, -1)
                          }
                          disabled={(product.stock ?? 0) === 0}
                          title="إنقاص المخزون"
                        >
                          −
                        </button>

                        <button
                          type="button"
                          className="delete-product-button"
                          onClick={() =>
                            deleteProduct(product.id)
                          }
                        >
                          حذف
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </section>

      {/* إدارة الطلبات */}
      <section className="admin-orders-card">
        <div className="admin-products-heading">
          <h2>إدارة طلبات الزبائن</h2>
          <span>عدد الطلبات: {orders.length}</span>
        </div>

        {orders.length === 0 ? (
          <p className="admin-empty">
            لا توجد طلبات حتى الآن.
          </p>
        ) : (
          <div className="admin-orders-list">
            {orders.map((order) => (
              <article className="admin-order-card" key={order.id}>

                <div className="admin-order-header">
                  <div>
                    <span className="order-label">
                      رقم الطلب
                    </span>
                    <h3>#{order.id}</h3>
                  </div>

                  <span className={`order-status ${order.status}`}>
                    {getStatusLabel(order.status)}
                  </span>
                </div>

                <div className="admin-order-customer">
                  <h4>بيانات الزبون</h4>

                  <p>
                    <strong>الاسم:</strong>{' '}
                    {order.customer?.name || 'غير محدد'}
                  </p>

                  <p>
                    <strong>الهاتف:</strong>{' '}
                    {order.customer?.phone || 'غير محدد'}
                  </p>

                  <p>
                    <strong>المدينة:</strong>{' '}
                    {order.customer?.city || 'غير محدد'}
                  </p>

                  <p>
                    <strong>العنوان:</strong>{' '}
                    {order.customer?.address || 'غير محدد'}
                  </p>

                  {order.customer?.notes && (
                    <p>
                      <strong>ملاحظات:</strong>{' '}
                      {order.customer.notes}
                    </p>
                  )}
                </div>

                <div className="admin-order-items">
                  <h4>المنتجات المطلوبة</h4>

                  {order.items?.map((item, index) => (
                    <div
                      className="admin-order-item"
                      key={`${item.id}-${index}`}
                    >
                      <span>{item.name}</span>
                      <span>الكمية: {item.quantity}</span>
                      <span>
                        ${(item.price * item.quantity).toFixed(2)}
                      </span>
                    </div>
                  ))}
                </div>

                <div className="admin-order-footer">
                  <div>
                    <span>الإجمالي</span>
                    <strong>
                      ${Number(order.total || 0).toFixed(2)}
                    </strong>
                  </div>

                  <div className="admin-status-control">
                    <label htmlFor={`status-${order.id}`}>
                      تغيير حالة الطلب
                    </label>

                    <select
                      id={`status-${order.id}`}
                      value={order.status}
                      onChange={(event) =>
                        changeOrderStatus(
                          order.id,
                          event.target.value
                        )
                      }
                    >
                      <option value="pending">قيد الانتظار</option>
                      <option value="processing">قيد التجهيز</option>
                      <option value="shipped">تم الشحن</option>
                      <option value="delivered">تم التوصيل</option>
                      <option value="cancelled">ملغي</option>
                    </select>
                  </div>
                </div>

              </article>
            ))}
          </div>
        )}
      </section>

    </main>
  );
}

export default Admin;
