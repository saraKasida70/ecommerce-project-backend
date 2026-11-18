const API_BASE = '/api/v1';
const AUTH_TOKEN_KEY = 'ecommerce_jwt';
const CART_KEY = 'ecommerce_cart';

function getToken() {
    return sessionStorage.getItem(AUTH_TOKEN_KEY) || '';
}

function setToken(token) {
    if (token) {
        sessionStorage.setItem(AUTH_TOKEN_KEY, token);
        return;
    }
    sessionStorage.removeItem(AUTH_TOKEN_KEY);
}

function setUserBadge(user) {
    const badge = document.getElementById('userBadge');
    const navUserBadge = document.getElementById('navUserBadge');
    const loginLink = document.getElementById('loginLink');
    const registerLink = document.getElementById('registerLink');
    const logoutBtn = document.getElementById('logoutBtn');

    if (!badge || !navUserBadge || !loginLink || !registerLink || !logoutBtn) {
        return;
    }

    if (user) {
        badge.textContent = user.name || user.email || 'Customer';
        navUserBadge.classList.remove('d-none');
        loginLink.classList.add('d-none');
        registerLink.classList.add('d-none');
        logoutBtn.classList.remove('d-none');
    } else {
        navUserBadge.classList.add('d-none');
        loginLink.classList.remove('d-none');
        registerLink.classList.remove('d-none');
        logoutBtn.classList.add('d-none');
    }
}

function showAlert(message, type = 'success') {
    const alert = document.getElementById('appAlert');
    if (!alert) {
        return;
    }

    alert.className = `alert alert-${type} mt-3`;
    alert.textContent = message;
    alert.classList.remove('d-none');
}

function clearAlert() {
    const alert = document.getElementById('appAlert');
    if (!alert) {
        return;
    }
    alert.classList.add('d-none');
    alert.textContent = '';
}

function authHeaders(additional = {}) {
    const token = getToken();
    return {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...additional,
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
    };
}

function apiFetch(path, options = {}) {
    const url = `${API_BASE}${path}`;
    return fetch(url, {
        credentials: 'same-origin',
        headers: authHeaders(),
        ...options,
        headers: {
            ...authHeaders(),
            ...(options.headers || {}),
        },
    }).then(async (response) => {
        const text = await response.text();
        const payload = text ? JSON.parse(text) : {};

        if (!response.ok) {
            const message = payload?.message || 'Request failed.';
            throw new Error(message);
        }

        return payload;
    });
}

function getCurrentUser() {
    const token = getToken();
    if (!token) {
        return Promise.resolve(null);
    }
    return apiFetch('/user').then((result) => result.data || result.user || null).catch(() => null);
}

function redirectToLogin() {
    window.location.href = '/login';
}

function isProtectedPage() {
    return ['cart', 'checkout', 'orders', 'notifications'].some((segment) => window.location.pathname.includes(segment));
}

function attachAuthUI() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            try {
                if (getToken()) {
                    await apiFetch('/logout', { method: 'POST' });
                }
            } catch (error) {
                console.warn('Logout request failed:', error);
            } finally {
                setToken('');
                setUserBadge(null);
                redirectToLogin();
            }
        });
    }

    getCurrentUser().then((user) => {
        setUserBadge(user);
        if (isProtectedPage() && !user) {
            redirectToLogin();
        }
    });
}

function handleLoginSubmit() {
    const form = document.getElementById('loginForm');
    if (!form) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearAlert();

        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;

        try {
            const response = await apiFetch('/login', {
                method: 'POST',
                body: JSON.stringify({ email, password }),
            });

            if (response.access_token) {
                setToken(response.access_token);
                setUserBadge(response.user || null);
                window.location.href = '/products';
            }
        } catch (error) {
            showAlert(error.message || 'Login failed.', 'danger');
        }
    });
}

function handleRegisterSubmit() {
    const form = document.getElementById('registerForm');
    if (!form) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearAlert();

        const name = document.getElementById('registerName').value.trim();
        const email = document.getElementById('registerEmail').value.trim();
        const password = document.getElementById('registerPassword').value;
        const passwordConfirmation = document.getElementById('registerPasswordConfirmation').value;

        if (password !== passwordConfirmation) {
            showAlert('Passwords do not match.', 'warning');
            return;
        }

        try {
            const response = await apiFetch('/register', {
                method: 'POST',
                body: JSON.stringify({
                    name,
                    email,
                    password,
                    password_confirmation: passwordConfirmation,
                }),
            });

            if (response.access_token) {
                setToken(response.access_token);
                setUserBadge(response.user || null);
                window.location.href = '/products';
            }
        } catch (error) {
            showAlert(error.message || 'Registration failed.', 'danger');
        }
    });
}

function getCart() {
    try {
        const raw = sessionStorage.getItem(CART_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (error) {
        return [];
    }
}

function saveCart(cart) {
    sessionStorage.setItem(CART_KEY, JSON.stringify(cart));
}

function addToCart(product) {
    const cart = getCart();
    const existing = cart.find((item) => item.id === product.id);

    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: Number(product.price),
            quantity: 1,
        });
    }

    saveCart(cart);
    showAlert(`${product.name} added to cart.`, 'success');
}

function renderProducts() {
    const container = document.getElementById('productsContainer');
    const loading = document.getElementById('productsLoading');

    if (!container) {
        return;
    }

    apiFetch('/products')
        .then((response) => {
            const products = response.data || [];

            if (loading) {
                loading.classList.add('d-none');
            }

            if (!products.length) {
                container.innerHTML = '<div class="col-12"><div class="alert alert-warning">No products available.</div></div>';
                return;
            }

            container.innerHTML = products.map((product) => `
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary-subtle text-primary-emphasis">${product.category || 'General'}</span>
                                <strong>$${Number(product.price).toFixed(2)}</strong>
                            </div>
                            <h5 class="card-title">${product.name}</h5>
                            <p class="card-text text-muted flex-grow-1">${product.description || 'No description available.'}</p>
                            <button class="btn btn-primary add-to-cart" data-product='${JSON.stringify(product).replace(/'/g, '&apos;')}' type="button">Add to cart</button>
                        </div>
                    </div>
                </div>
            `).join('');

            document.querySelectorAll('.add-to-cart').forEach((button) => {
                button.addEventListener('click', () => {
                    const product = JSON.parse(button.dataset.product.replace(/&apos;/g, "'"));
                    addToCart(product);
                });
            });
        })
        .catch((error) => {
            if (loading) {
                loading.classList.add('d-none');
            }
            container.innerHTML = `<div class="col-12"><div class="alert alert-danger">${error.message}</div></div>`;
        });
}

function renderCart() {
    const container = document.getElementById('cartContainer');
    if (!container) {
        return;
    }

    const cart = getCart();

    if (!cart.length) {
        container.innerHTML = '<div class="alert alert-info">Your cart is empty.</div>';
        return;
    }

    const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    container.innerHTML = `
        <div class="card shadow-sm border-0">
            <div class="card-body">
                ${cart.map((item) => `
                    <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                        <div>
                            <h6 class="mb-1">${item.name}</h6>
                            <small class="text-muted">Qty: ${item.quantity}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <strong>$${(item.price * item.quantity).toFixed(2)}</strong>
                            <button class="btn btn-sm btn-outline-danger remove-cart-item" data-id="${item.id}" type="button">Remove</button>
                        </div>
                    </div>
                `).join('')}
                <div class="d-flex justify-content-between mt-4">
                    <strong>Total</strong>
                    <strong>$${subtotal.toFixed(2)}</strong>
                </div>
                <a href="/checkout" class="btn btn-primary w-100 mt-3">Proceed to Checkout</a>
            </div>
        </div>
    `;

    document.querySelectorAll('.remove-cart-item').forEach((button) => {
        button.addEventListener('click', () => {
            const itemId = Number(button.dataset.id);
            const nextCart = getCart().filter((item) => item.id !== itemId);
            saveCart(nextCart);
            renderCart();
        });
    });
}

function renderCheckout() {
    const itemsContainer = document.getElementById('checkoutItems');
    const itemCount = document.getElementById('checkoutItemCount');
    const totalElement = document.getElementById('checkoutTotal');
    const placeOrderBtn = document.getElementById('placeOrderBtn');

    if (!itemsContainer || !itemCount || !totalElement || !placeOrderBtn) {
        return;
    }

    const cart = getCart();

    if (!cart.length) {
        itemsContainer.innerHTML = '<div class="alert alert-info">Your cart is empty. Add products before checkout.</div>';
        itemCount.textContent = '0';
        totalElement.textContent = '$0.00';
        placeOrderBtn.disabled = true;
        return;
    }

    const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    itemCount.textContent = String(cart.reduce((sum, item) => sum + item.quantity, 0));
    totalElement.textContent = `$${subtotal.toFixed(2)}`;

    itemsContainer.innerHTML = cart.map((item) => `
        <div class="d-flex justify-content-between align-items-center border-bottom py-3">
            <div>
                <h6 class="mb-1">${item.name}</h6>
                <small class="text-muted">Quantity: ${item.quantity}</small>
            </div>
            <strong>$${(item.price * item.quantity).toFixed(2)}</strong>
        </div>
    `).join('');

    placeOrderBtn.disabled = false;
    placeOrderBtn.addEventListener('click', async () => {
        try {
            const payload = {
                items: cart.map((item) => ({
                    product_id: item.id,
                    quantity: item.quantity,
                })),
            };

            const response = await apiFetch('/orders', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            saveCart([]);
            renderCheckout();
            showAlert(response.message || 'Order placed successfully.', 'success');
            window.location.href = '/orders';
        } catch (error) {
            showAlert(error.message || 'Unable to place order.', 'danger');
        }
    }, { once: true });
}

function renderOrders() {
    const loading = document.getElementById('ordersLoading');
    const container = document.getElementById('ordersContainer');

    if (!container) {
        return;
    }

    apiFetch('/orders')
        .then((response) => {
            if (loading) {
                loading.classList.add('d-none');
            }

            const orders = response.data || [];

            if (!orders.length) {
                container.innerHTML = '<div class="alert alert-info">You have no orders yet.</div>';
                return;
            }

            container.innerHTML = orders.map((order) => `
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">Order #${order.id}</h5>
                            <span class="badge bg-secondary">${order.status || 'pending'}</span>
                        </div>
                        <p class="mb-2"><strong>Total:</strong> $${Number(order.total || 0).toFixed(2)}</p>
                        <div class="small text-muted">Items: ${(order.items || []).length}</div>
                    </div>
                </div>
            `).join('');
        })
        .catch((error) => {
            if (loading) {
                loading.classList.add('d-none');
            }
            container.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        });
}

function getAdminPagesAccess() {
    const token = getToken();
    if (!token) {
        return false;
    }
    return getCurrentUser().then((user) => !!(user && user.role === 'admin'));
}

async function enforceAdminAccess() {
    const isAdminPage = ['/admin', '/admin/orders', '/admin/inventory', '/admin/users'].some((path) => window.location.pathname.startsWith(path));
    if (!isAdminPage) {
        return;
    }

    const user = await getCurrentUser();

    if (!user) {
        window.location.href = '/login';
        return;
    }

    if (user.role !== 'admin') {
        window.location.href = '/products';
        return;
    }
}

async function renderAdminDashboard() {
    const statusBox = document.getElementById('adminStatusBox');
    const orderCount = document.getElementById('adminOrderCount');
    const productCount = document.getElementById('adminProductCount');

    if (!statusBox) {
        return;
    }

    try {
        const user = await getCurrentUser();
        if (!user || user.role !== 'admin') {
            statusBox.className = 'alert alert-danger mb-0';
            statusBox.textContent = 'Access denied. Admin rights required.';
            return;
        }

        statusBox.className = 'alert alert-success mb-0';
        statusBox.textContent = 'Admin access confirmed.';

        if (orderCount) {
            orderCount.textContent = 'Checking...';
        }
        if (productCount) {
            productCount.textContent = 'Checking...';
        }

        const [ordersResponse, productsResponse] = await Promise.all([
            apiFetch('/orders'),
            apiFetch('/products'),
        ]);

        if (orderCount) {
            orderCount.textContent = String((ordersResponse.data || []).length || 0);
        }
        if (productCount) {
            productCount.textContent = String((productsResponse.data || []).length || 0);
        }
    } catch (error) {
        statusBox.className = 'alert alert-danger mb-0';
        statusBox.textContent = error.message || 'Unable to load admin dashboard.';
    }
}

async function renderAdminOrders() {
    const container = document.getElementById('adminOrdersContainer');
    const statusBox = document.getElementById('adminOrdersStatus');

    if (!container) {
        return;
    }

    try {
        const response = await apiFetch('/orders');
        const orders = response.data || [];

        if (!orders.length) {
            container.innerHTML = '<div class="alert alert-info">No orders found.</div>';
            statusBox.textContent = 'No orders available.';
            return;
        }

        container.innerHTML = orders.map((order) => `
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Order #${order.id}</h5>
                        <span class="badge bg-secondary">${order.status || 'pending'}</span>
                    </div>
                    <p class="mb-3"><strong>Total:</strong> $${Number(order.total || 0).toFixed(2)}</p>
                    <div class="row g-2 align-items-center">
                        <div class="col-md-8">
                            <select class="form-select order-status-select" data-order-id="${order.id}" data-current-status="${order.status || 'pending'}">
                                <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>pending</option>
                                <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>processing</option>
                                <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>shipped</option>
                                <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>delivered</option>
                                <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100 update-order-status" data-order-id="${order.id}" type="button">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        document.querySelectorAll('.update-order-status').forEach((button) => {
            button.addEventListener('click', async () => {
                const orderId = button.dataset.orderId;
                const select = document.querySelector(`.order-status-select[data-order-id="${orderId}"]`);
                const status = select ? select.value : 'pending';

                try {
                    const res = await apiFetch(`/admin/orders/${orderId}/status`, {
                        method: 'PATCH',
                        body: JSON.stringify({ status }),
                    });

                    statusBox.textContent = res.message || 'Order status updated.';
                    statusBox.className = 'alert alert-success';
                    await renderAdminOrders();
                } catch (error) {
                    statusBox.textContent = error.message || 'Failed to update order status.';
                    statusBox.className = 'alert alert-danger';
                }
            });
        });
    } catch (error) {
        statusBox.textContent = error.message || 'Unable to load orders.';
        statusBox.className = 'alert alert-danger';
        container.innerHTML = '<div class="alert alert-danger">Unable to load orders.</div>';
    }
}

async function renderInventoryManagement() {
    const getBtn = document.getElementById('getStockBtn');
    const addBtn = document.getElementById('addStockBtn');
    const reserveBtn = document.getElementById('reserveStockBtn');
    const releaseBtn = document.getElementById('releaseStockBtn');

    if (!getBtn || !addBtn || !reserveBtn || !releaseBtn) {
        return;
    }

    getBtn.addEventListener('click', async () => {
        const productId = Number(document.getElementById('stockProductId').value || 0);
        const box = document.getElementById('stockResult');

        try {
            const response = await apiFetch(`/admin/inventory/${productId}`);
            box.innerHTML = `<pre class="mb-0">${JSON.stringify(response, null, 2)}</pre>`;
        } catch (error) {
            box.innerHTML = `<div class="alert alert-danger mb-0">${error.message}</div>`;
        }
    });

    addBtn.addEventListener('click', async () => {
        const productId = Number(document.getElementById('addStockProductId').value || 0);
        const quantity = Number(document.getElementById('addStockQuantity').value || 0);
        const box = document.getElementById('addStockResult');

        try {
            const response = await apiFetch('/admin/inventory/add', {
                method: 'POST',
                body: JSON.stringify({ product_id: productId, quantity }),
            });
            box.innerHTML = `<pre class="mb-0">${JSON.stringify(response, null, 2)}</pre>`;
        } catch (error) {
            box.innerHTML = `<div class="alert alert-danger mb-0">${error.message}</div>`;
        }
    });

    reserveBtn.addEventListener('click', async () => {
        const orderReference = document.getElementById('reserveOrderRef').value;
        const itemsText = document.getElementById('reserveItemsInput').value;
        const box = document.getElementById('reserveResult');

        try {
            const items = JSON.parse(itemsText);
            const response = await apiFetch('/admin/inventory/reserve', {
                method: 'POST',
                body: JSON.stringify({ order_reference: orderReference, items }),
            });
            box.innerHTML = `<pre class="mb-0">${JSON.stringify(response, null, 2)}</pre>`;
        } catch (error) {
            box.innerHTML = `<div class="alert alert-danger mb-0">${error.message}</div>`;
        }
    });

    releaseBtn.addEventListener('click', async () => {
        const orderReference = document.getElementById('reserveOrderRef').value;
        const box = document.getElementById('reserveResult');

        try {
            const response = await apiFetch('/admin/inventory/release', {
                method: 'POST',
                body: JSON.stringify({ reservation_id: orderReference }),
            });
            box.innerHTML = `<pre class="mb-0">${JSON.stringify(response, null, 2)}</pre>`;
        } catch (error) {
            box.innerHTML = `<div class="alert alert-danger mb-0">${error.message}</div>`;
        }
    });
}

async function renderInventoryTestPage() {
    const resultBox = document.getElementById('inventoryTestResult');
    const getBtn = document.getElementById('testGetStockBtn');
    const addBtn = document.getElementById('testAddStockBtn');
    const reserveBtn = document.getElementById('testReserveBtn');
    const releaseBtn = document.getElementById('testReleaseBtn');

    if (!resultBox) {
        return;
    }

    const renderError = (message) => {
        resultBox.className = 'mt-4 alert alert-danger';
        resultBox.textContent = message;
    };

    const renderSuccess = (message) => {
        resultBox.className = 'mt-4 alert alert-success';
        resultBox.textContent = message;
    };

    getBtn?.addEventListener('click', async () => {
        try {
            const productId = Number(document.getElementById('testProductId').value || 0);
            const response = await apiFetch(`/admin/inventory/${productId}`);
            renderSuccess(`GET STOCK\n${JSON.stringify(response, null, 2)}`);
        } catch (error) {
            renderError(error.message || 'Get stock failed.');
        }
    });

    addBtn?.addEventListener('click', async () => {
        try {
            const productId = Number(document.getElementById('testProductId').value || 0);
            const quantity = Number(document.getElementById('testQuantity').value || 0);
            const response = await apiFetch('/admin/inventory/add', {
                method: 'POST',
                body: JSON.stringify({ product_id: productId, quantity }),
            });
            renderSuccess(`ADD STOCK\n${JSON.stringify(response, null, 2)}`);
        } catch (error) {
            renderError(error.message || 'Add stock failed.');
        }
    });

    reserveBtn?.addEventListener('click', async () => {
        try {
            const productId = Number(document.getElementById('testProductId').value || 0);
            const quantity = Number(document.getElementById('testQuantity').value || 0);
            const ref = document.getElementById('testRef').value;
            const response = await apiFetch('/admin/inventory/reserve', {
                method: 'POST',
                body: JSON.stringify({
                    order_reference: ref,
                    items: [{ product_id: productId, quantity }],
                }),
            });
            renderSuccess(`RESERVE\n${JSON.stringify(response, null, 2)}`);
        } catch (error) {
            renderError(error.message || 'Reserve stock failed.');
        }
    });

    releaseBtn?.addEventListener('click', async () => {
        try {
            const ref = document.getElementById('testRef').value;
            const response = await apiFetch('/admin/inventory/release', {
                method: 'POST',
                body: JSON.stringify({ reservation_id: ref }),
            });
            renderSuccess(`RELEASE\n${JSON.stringify(response, null, 2)}`);
        } catch (error) {
            renderError(error.message || 'Release stock failed.');
        }
    });
}

function normalizeNotificationData(notification) {
    if (!notification || !notification.data) {
        return {};
    }

    if (typeof notification.data === 'object') {
        return notification.data;
    }

    try {
        return JSON.parse(notification.data);
    } catch (error) {
        return { message: String(notification.data) };
    }
}

async function renderNotifications() {
    const container = document.getElementById('notificationsContainer');
    const statusBox = document.getElementById('notificationsStatus');
    const markAllBtn = document.getElementById('markAllNotificationsRead');

    if (!container) {
        return;
    }

    try {
        const response = await apiFetch('/notifications');
        const notifications = response.data || [];

        if (!notifications.length) {
            statusBox.textContent = 'You have no notifications.';
            container.innerHTML = '<div class="alert alert-info">No notifications available.</div>';
            return;
        }

        const unreadCount = notifications.filter((notification) => !notification.read_at).length;
        statusBox.textContent = unreadCount > 0 ? `${unreadCount} unread notifications.` : 'All notifications are read.';

        container.innerHTML = notifications.map((notification) => {
            const data = normalizeNotificationData(notification);
            const message = data.message || data.title || data.body || notification.type || 'Notification';
            const isUnread = !notification.read_at;

            return `
                <div class="card border-0 shadow-sm mb-3 ${isUnread ? 'border-start border-4 border-primary' : ''}">
                    <div class="card-body d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge ${isUnread ? 'bg-primary' : 'bg-secondary'}">${isUnread ? 'Unread' : 'Read'}</span>
                                <small class="text-muted">${notification.type || 'info'}</small>
                            </div>
                            <h6 class="mb-1">${message}</h6>
                            <small class="text-muted">${notification.created_at ? new Date(notification.created_at).toLocaleString() : ''}</small>
                        </div>
                        <button
                            class="btn btn-sm ${isUnread ? 'btn-outline-primary' : 'btn-outline-secondary'} mark-notification-read"
                            type="button"
                            data-id="${notification.id}"
                            ${isUnread ? '' : 'disabled'}
                        >
                            ${isUnread ? 'Mark as read' : 'Read'}
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        document.querySelectorAll('.mark-notification-read').forEach((button) => {
            button.addEventListener('click', async () => {
                const notificationId = button.dataset.id;

                try {
                    const result = await apiFetch(`/notifications/${notificationId}/read`, {
                        method: 'PATCH',
                    });
                    showAlert(result.message || 'Notification marked as read.', 'success');
                    await renderNotifications();
                } catch (error) {
                    showAlert(error.message || 'Unable to mark notification as read.', 'danger');
                }
            });
        });

        markAllBtn?.addEventListener('click', async () => {
            try {
                const result = await apiFetch('/notifications/read-all', {
                    method: 'PATCH',
                });
                showAlert(result.message || 'All notifications marked as read.', 'success');
                await renderNotifications();
            } catch (error) {
                showAlert(error.message || 'Unable to mark all notifications as read.', 'danger');
            }
        });
    } catch (error) {
        statusBox.textContent = error.message || 'Unable to load notifications.';
        statusBox.className = 'alert alert-danger';
        container.innerHTML = `<div class="alert alert-danger">${error.message || 'Unable to load notifications.'}</div>`;
    }
}

async function renderAdminUsers() {
    const form = document.getElementById('adminUserForm');
    const statusBox = document.getElementById('adminUsersStatus');

    if (!form) {
        return;
    }

    try {
        const user = await getCurrentUser();

        if (!user || user.role !== 'admin') {
            window.location.href = '/products';
            return;
        }

        statusBox.textContent = 'Admin access confirmed. The current API supports creating users only.';
        statusBox.className = 'alert alert-info';
    } catch (error) {
        statusBox.textContent = error.message || 'Unable to verify admin access.';
        statusBox.className = 'alert alert-danger';
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(form);
        const payload = {
            name: formData.get('name')?.toString().trim(),
            email: formData.get('email')?.toString().trim(),
            password: formData.get('password')?.toString(),
            password_confirmation: formData.get('password_confirmation')?.toString(),
            role: formData.get('role')?.toString(),
        };

        try {
            const result = await apiFetch('/admin/users', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            statusBox.textContent = result.message || 'User created successfully.';
            statusBox.className = 'alert alert-success';
            form.reset();
        } catch (error) {
            const message = error.message || 'Unable to create user.';
            statusBox.textContent = message;
            statusBox.className = 'alert alert-danger';
        }
    });
}

function initPage() {
    attachAuthUI();
    handleLoginSubmit();
    handleRegisterSubmit();

    if (window.location.pathname.startsWith('/products')) {
        renderProducts();
    }

    if (window.location.pathname.startsWith('/cart')) {
        renderCart();
    }

    if (window.location.pathname.startsWith('/checkout')) {
        renderCheckout();
    }

    if (window.location.pathname.startsWith('/orders')) {
        renderOrders();
    }

    if (window.location.pathname.startsWith('/notifications')) {
        renderNotifications();
    }

    if (window.location.pathname.startsWith('/admin')) {
        enforceAdminAccess().then(() => {
            if (window.location.pathname === '/admin') {
                renderAdminDashboard();
            }
            if (window.location.pathname === '/admin/orders') {
                renderAdminOrders();
            }
            if (window.location.pathname === '/admin/inventory') {
                renderInventoryManagement();
            }
            if (window.location.pathname === '/admin/users') {
                renderAdminUsers();
            }
        });
    }

    if (window.location.pathname.startsWith('/inventory/test')) {
        enforceAdminAccess().then(() => {
            renderInventoryTestPage();
        });
    }
}

window.addEventListener('DOMContentLoaded', initPage);
