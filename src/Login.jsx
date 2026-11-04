
import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import './Login.css';

function Login() {
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });

  const [error, setError] = useState('');

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
    setError('');
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    if (!formData.email || !formData.password) {
      setError('يرجى تعبئة جميع الحقول.');
      return;
    }

    // تسجيل دخول تجريبي، سنربطه بالـ Backend لاحقاً
    setError('تسجيل الدخول غير مربوط بالخادم حالياً.');
  };

  return (
    <div className="login-page">
      <div className="login-card">
        <div className="login-icon">🛍️</div>

        <h1>مرحباً بعودتك!</h1>
        <p className="login-subtitle">
          سجّلي الدخول للمتابعة والتسوق
        </p>

        <form onSubmit={handleSubmit}>
          <div className="login-form-group">
            <label htmlFor="email">البريد الإلكتروني</label>
            <input
              id="email"
              type="email"
              name="email"
              placeholder="example@email.com"
              value={formData.email}
              onChange={handleChange}
            />
          </div>

          <div className="login-form-group">
            <label htmlFor="password">كلمة المرور</label>
            <input
              id="password"
              type="password"
              name="password"
              placeholder="أدخلي كلمة المرور"
              value={formData.password}
              onChange={handleChange}
            />
          </div>

          {error && <p className="login-error">{error}</p>}

          <button type="submit" className="login-button">
            تسجيل الدخول
          </button>
        </form>

        <div className="login-footer">
          ليس لديك حساب؟
          <Link to="/register">إنشاء حساب جديد</Link>
        </div>

        <button
          className="back-home-button"
          onClick={() => navigate('/')}
        >
          العودة إلى المتجر
        </button>
      </div>
    </div>
  );
}

export default Login;
