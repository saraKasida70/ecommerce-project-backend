
import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import './Register.css';

function Register() {
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
  });

  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  function handleChange(event) {
    const { name, value } = event.target;

    setFormData((current) => ({
      ...current,
      [name]: value,
    }));
  }

  function handleSubmit(event) {
    event.preventDefault();

    setMessage('');
    setError('');

    if (
      !formData.name.trim() ||
      !formData.email.trim() ||
      !formData.password ||
      !formData.confirmPassword
    ) {
      setError('يرجى تعبئة جميع الحقول.');
      return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      setError('يرجى إدخال بريد إلكتروني صحيح.');
      return;
    }

    if (formData.password.length < 6) {
      setError('يجب أن تتكون كلمة المرور من 6 أحرف على الأقل.');
      return;
    }

    if (formData.password !== formData.confirmPassword) {
      setError('كلمتا المرور غير متطابقتين.');
      return;
    }

    setMessage('تم التحقق من البيانات بنجاح، التسجيل غير مربوط بالخادم حالياً.');

    // التسجيل الفعلي سنربطه بالـ Backend لاحقاً.
  }

  return (
    <main className="register-page">
      <div className="register-card">
        <div className="register-icon">👤</div>

        <h1>إنشاء حساب جديد</h1>

        <p className="register-subtitle">
          أنشئ حسابك وابدأ تجربة التسوق معنا
        </p>

        <form onSubmit={handleSubmit}>
          <div className="register-field">
            <label htmlFor="name">الاسم الكامل</label>
            <input
              id="name"
              name="name"
              type="text"
              placeholder="أدخل اسمك الكامل"
              value={formData.name}
              onChange={handleChange}
            />
          </div>

          <div className="register-field">
            <label htmlFor="email">البريد الإلكتروني</label>
            <input
              id="email"
              name="email"
              type="email"
              placeholder="example@email.com"
              value={formData.email}
              onChange={handleChange}
            />
          </div>

          <div className="register-field">
            <label htmlFor="password">كلمة المرور</label>
            <input
              id="password"
              name="password"
              type="password"
              placeholder="أدخل كلمة المرور"
              value={formData.password}
              onChange={handleChange}
            />
          </div>

          <div className="register-field">
            <label htmlFor="confirmPassword">تأكيد كلمة المرور</label>
            <input
              id="confirmPassword"
              name="confirmPassword"
              type="password"
              placeholder="أعد إدخال كلمة المرور"
              value={formData.confirmPassword}
              onChange={handleChange}
            />
          </div>

          {error && (
            <div className="register-error">
              {error}
            </div>
          )}

          {message && (
            <div className="register-success">
              {message}
            </div>
          )}

          <button type="submit" className="register-submit">
            إنشاء الحساب
          </button>
        </form>

        <div className="register-login">
          لديك حساب بالفعل؟
          <Link to="/login"> تسجيل الدخول</Link>
        </div>

        <button
          className="register-back"
          onClick={() => navigate('/')}
        >
          العودة إلى المتجر
        </button>
      </div>
    </main>
  );
}

export default Register;
