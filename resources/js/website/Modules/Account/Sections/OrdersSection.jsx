import { Link } from '@inertiajs/react';

export default function OrdersSection({ orders = [] }) {
    if (orders.length === 0) {
        return (
            <section className="account-panel account-empty" aria-label="Order history">
                <p>No orders yet. Start shopping to see them here.</p>
                <Link href="/products" className="btn btn--primary">
                    Browse products
                </Link>
            </section>
        );
    }

    return (
        <section className="account-panel" aria-label="Order history">
            <ul className="account-order-list">
                {orders.map((order) => (
                    <li key={order.id} className="account-order">
                        <div className="account-order__top">
                            <div>
                                <p className="account-order__id">{order.id}</p>
                                <p className="account-order__date">Placed {order.placedAt}</p>
                            </div>
                            <span className={`account-badge account-badge--${order.statusTone}`}>
                                {order.status}
                            </span>
                        </div>
                        <p className="account-order__summary">{order.itemsSummary}</p>
                        <div className="account-order__foot">
                            <p className="account-order__total">
                                {order.total}
                                <span>
                                    · {order.itemCount} item{order.itemCount === 1 ? '' : 's'}
                                </span>
                            </p>
                            <Link href={order.href} className="account-order__link">
                                View product
                            </Link>
                        </div>
                    </li>
                ))}
            </ul>
        </section>
    );
}
