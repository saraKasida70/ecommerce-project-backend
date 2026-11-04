
import { useMemo, useState } from 'react';
import {
  Routes,
  Route,
  Link,
  useNavigate,
} from 'react-router-dom';

import './App.css';
import Cart from './Cart';
import Checkout from './Checkout';
import Login from './Login';
import Register from './Register';
import Admin from './Admin';
import MyOrders from './MyOrders';

// المنتجات الأولية
const initialProducts = [
  {
    id: 1,
    name: 'سماعات لاسلكية',
    category: 'إلكترونيات',
    price: 35,
    image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600',
    description: 'سماعات بجودة صوت عالية وتصميم مريح، تمنحك تجربة استماع مميزة.',
    stock: 10,
  },
  {
    id: 2,
    name: 'ساعة ذكية',
    category: 'إلكترونيات',
    price: 55,
    image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600',
    description: 'ساعة ذكية لمتابعة نشاطك اليومي مع تصميم أنيق وعصري.',
    stock: 8,
  },
  {
    id: 3,
    name: 'حقيبة أنيقة',
    category: 'أزياء',
    price: 42,
    image: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600',
    description: 'حقيبة عملية وأنيقة مناسبة للاستخدام اليومي.',
    stock: 12,
  },
  {
    id: 4,
    name: 'حذاء رياضي',
    category: 'أزياء',
    price: 60,
    image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600',
    description: 'حذاء رياضي مريح بتصميم عصري يناسب مختلف الأنشطة.',
    stock: 6,
  },
  {
    id: 5,
    name: 'مصباح منزلي',
    category: 'المنزل',
    price: 28,
    image: 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600',
    description: 'مصباح أنيق يضيف لمسة جمالية وإضاءة مميزة لمنزلك.',
    stock: 9,
  },
  {
    id: 6,
    name: 'كرسي عصري',
    category: 'المنزل',
    price: 85,
    image: 'https://images.unsplash.com/photo-1503602642458-232111445657?w=600',
    description: 'كرسي مريح بتصميم بسيط وأنيق يناسب المنزل والمكتب.',
    stock: 4,
  },
];

const categories = ['الكل', 'إلكترونيات', 'أزياء', 'المنزل'];

function Home({ setCart, products }) {
  const [search, setSearch] = useState('');
  const [category, setCategory] = useState('الكل');
  const [message, setMessage] = useState('');
  const [selectedProduct, setSelectedProduct] = useState(null);

  // إعدادات تقسيم المنتجات إلى صفحات
  const [currentPage, setCurrentPage] = useState(1);
  const pageSize = 4;

  const filteredProducts = useMemo(() => {
    return products.filter((product) => {
      const matchesCategory =
        category === 'الكل' || product.category === category;

      const matchesSearch = product.name
        .toLowerCase()
        .includes(search.toLowerCase());

      return matchesCategory && matchesSearch;
    });
  }, [search, category, products]);

  // حساب عدد الصفحات والمنتجات الظاهرة
  const totalPages = Math.ceil(filteredProducts.length / pageSize);

  const startIndex = (currentPage - 1) * pageSize;

  const displayedProducts = filteredProducts.slice(
    startIndex,
    startIndex + pageSize
  );

  function addToCart(product) {
    setCart((currentCart) => {
      const existing = currentCart.find(
        (item) => item.id === product.id
      );

      if (existing) {
        return currentCart.map((item) =>
          item.id === product.id
            ? { ...item, quantity: item.quantity + 1 }
            : item
        );
      }

      return [...currentCart, { ...product, quantity: 1 }];
    });

    setMessage(`تمت إضافة ${product.name} إلى السلة`);

    setTimeout(() => {
      setMessage('');
    }, 2500);
  }

  return (
    <>
      <main className="container">
        <section className="welcome-section">
          <div>
            <p className="welcome-label">
              أهلاً بك في متجرنا 👋
            </p>

            <h1>اكتشف منتجاتنا</h1>

            <p className="welcome-description">
              تسوّق منتجاتك المفضلة بسهولة وبأفضل الأسعار.
            </p>
          </div>

          <div className="welcome-icon">🛍️</div>
        </section>

        <section className="search-section">
          <input
            type="search"
            placeholder="ابحث عن منتج..."
            value={search}
            onChange={(event) => {
              setSearch(event.target.value);
              setCurrentPage(1);
            }}
          />

          <span>🔍</span>
        </section>

        <section className="products-section">
          <div className="section-heading">
            <div>
              <h2>تسوق حسب التصنيف</h2>
              <p>اختر التصنيف المناسب لك</p>
            </div>
          </div>

          <div className="categories">
            {categories.map((item) => (
              <button
                key={item}
                className={
                  category === item ? 'category active' : 'category'
                }
                onClick={() => {
                  setCategory(item);
                  setCurrentPage(1);
                }}
              >
                {item}
              </button>
            ))}
          </div>

          <div className="section-heading product-heading">
            <div>
              <h2>منتجاتنا</h2>
              <p>{filteredProducts.length} منتجات متاحة</p>
            </div>
          </div>

          <div className="products-grid">
            {displayedProducts.map((product) => (
              <article
                className="product-card"
                key={product.id}
                onClick={() => setSelectedProduct(product)}
              >
                <div className="product-image">
                  <img src={product.image} alt={product.name} />
                </div>

                <div className="product-info">
                  <span className="product-category">
                    {product.category}
                  </span>

                  <h3>{product.name}</h3>

                  <p>{product.description}</p>

                  <div className="product-bottom">
                    <strong>${Number(product.price).toFixed(2)}</strong>

                    <button
                      onClick={(event) => {
                        event.stopPropagation();
                        addToCart(product);
                      }}
                    >
                      + أضف للسلة
                    </button>
                  </div>
                </div>
              </article>
            ))}
          </div>

          {totalPages > 1 && (
            <div className="pagination">
              <button
                disabled={currentPage === 1}
                onClick={() => setCurrentPage(currentPage - 1)}
              >
                السابق
              </button>

              {Array.from({ length: totalPages }, (_, index) => (
                <button
                  key={index + 1}
                  className={
                    currentPage === index + 1 ? 'active' : ''
                  }
                  onClick={() => setCurrentPage(index + 1)}
                >
                  {index + 1}
                </button>
              ))}

              <button
                disabled={currentPage === totalPages}
                onClick={() => setCurrentPage(currentPage + 1)}
              >
                التالي
              </button>
            </div>
          )}

          {filteredProducts.length === 0 && (
            <div className="empty-state">
              لا توجد منتجات تطابق بحثك.
            </div>
          )}
        </section>
      </main>

      {selectedProduct && (
        <div
          className="modal-overlay"
          onClick={() => setSelectedProduct(null)}
        >
          <div
            className="product-modal"
            onClick={(event) => event.stopPropagation()}
          >
            <button
              className="close-modal"
              onClick={() => setSelectedProduct(null)}
            >
              ✕
            </button>

            <img
              src={selectedProduct.image}
              alt={selectedProduct.name}
            />

            <span className="product-category">
              {selectedProduct.category}
            </span>

            <h2>{selectedProduct.name}</h2>

            <p>{selectedProduct.description}</p>

            <strong>
              ${Number(selectedProduct.price).toFixed(2)}
            </strong>

            <button
              className="modal-add-button"
              onClick={() => {
                addToCart(selectedProduct);
                setSelectedProduct(null);
              }}
            >
              أضف إلى السلة
            </button>
          </div>
        </div>
      )}

      {message && <div className="toast">{message}</div>}
    </>
  );
}

function App() {
  const [cart, setCart] = useState([]);
  const [products, setProducts] = useState(initialProducts);
  const [orders, setOrders] = useState([]);

  const navigate = useNavigate();

  const cartCount = cart.reduce(
    (total, item) => total + item.quantity,
    0
  );

  function updateQuantity(id, quantity) {
    setCart((currentCart) =>
      currentCart.map((item) =>
        item.id === id ? { ...item, quantity } : item
      )
    );
  }

  function removeFromCart(id) {
    setCart((currentCart) =>
      currentCart.filter((item) => item.id !== id)
    );
  }

  return (
    <div className="app" dir="rtl">
      <header className="navbar">
        <Link className="logo" to="/">
          <span className="logo-icon">🛍️</span>
          متجري
        </Link>

        <div className="nav-actions">
          <button
            className="login-button"
            onClick={() => navigate('/login')}
          >
            تسجيل الدخول
          </button>

          <button
            className="login-button"
            onClick={() => navigate('/my-orders')}
          >
            طلباتي
          </button>

          <button
            className="login-button"
            onClick={() => navigate('/admin')}
          >
            إدارة المنتجات
          </button>

          <button
            className="cart-button"
            onClick={() => navigate('/cart')}
          >
            🛒 السلة
            <span className="cart-count">{cartCount}</span>
          </button>
        </div>
      </header>

      <Routes>
        <Route
          path="/"
          element={
            <Home
              setCart={setCart}
              products={products}
            />
          }
        />

        <Route
          path="/cart"
          element={
            <Cart
              cart={cart}
              updateQuantity={updateQuantity}
              removeFromCart={removeFromCart}
            />
          }
        />

        <Route
          path="/checkout"
          element={
            <Checkout
              cart={cart}
              setOrders={setOrders}
            />
          }
        />

        <Route
          path="/login"
          element={<Login />}
        />

        <Route
          path="/register"
          element={<Register />}
        />

        <Route
          path="/admin"
          element={
            <Admin
              products={products}
              setProducts={setProducts}
              orders={orders}
              setOrders={setOrders}
            />
          }
        />

        <Route
          path="/my-orders"
          element={<MyOrders orders={orders} />}
        />

        <Route
          path="*"
          element={
            <main className="container empty-state">
              الصفحة غير موجودة.
              <p>
                <Link to="/">العودة إلى المتجر</Link>
              </p>
            </main>
          }
        />
      </Routes>

      <footer className="footer">
         2026
      </footer>
    </div>
  );
}

export default App;
