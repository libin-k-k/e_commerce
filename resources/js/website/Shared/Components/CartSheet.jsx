import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useId } from 'react';

export default function CartSheet({ open, onClose }) {
    const titleId = useId();
    const { cart, flash } = usePage().props;
    const items = cart?.items ?? [];
    const subtotalLabel = cart?.subtotalLabel ?? '₹0.00';

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.body.classList.add('is-sheet-open');
        window.addEventListener('keydown', onKeyDown);

        return () => {
            document.body.classList.remove('is-sheet-open');
            window.removeEventListener('keydown', onKeyDown);
        };
    }, [open, onClose]);

    const updateQty = (id, quantity) => {
        router.patch(`/cart/${id}`, { quantity }, { preserveScroll: true });
    };

    const removeItem = (id) => {
        router.delete(`/cart/${id}`, { preserveScroll: true });
    };

    return (
        <div
            className={`side-sheet${open ? ' is-open' : ''}`}
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            aria-hidden={!open}
        >
            <button type="button" className="side-sheet__backdrop" aria-label="Close cart" onClick={onClose} />
            <div className="side-sheet__panel">
                <div className="side-sheet__header">
                    <p id={titleId} className="side-sheet__title">
                        Cart
                    </p>
                    <button type="button" className="icon-btn" aria-label="Close cart" onClick={onClose}>
                        <CloseIcon />
                    </button>
                </div>

                {flash?.success && open ? (
                    <p className="side-sheet__flash" role="status">
                        {flash.success}
                    </p>
                ) : null}

                <div className="side-sheet__body">
                    {items.length === 0 ? (
                        <div className="side-sheet__empty">
                            <p>Your cart is empty.</p>
                            <button type="button" className="btn btn--primary" onClick={onClose}>
                                Continue shopping
                            </button>
                        </div>
                    ) : (
                        <ul className="sheet-list">
                            {items.map((item) => (
                                <li key={item.id} className="sheet-list__item">
                                    <Link
                                        href={`/products/${item.slug}`}
                                        className="sheet-list__media"
                                        onClick={onClose}
                                    >
                                        {item.image ? <img src={item.image} alt="" /> : null}
                                    </Link>
                                    <div className="sheet-list__body">
                                        <Link
                                            href={`/products/${item.slug}`}
                                            className="sheet-list__name"
                                            onClick={onClose}
                                        >
                                            {item.name}
                                        </Link>
                                        {item.size || item.color ? (
                                            <p className="sheet-list__meta">
                                                {[item.size, item.color].filter(Boolean).join(' · ')}
                                            </p>
                                        ) : null}
                                        <p className="sheet-list__price">{item.line_total_label}</p>
                                        <div className="sheet-list__qty">
                                            <button
                                                type="button"
                                                className="sheet-list__qty-btn"
                                                aria-label="Decrease quantity"
                                                onClick={() => updateQty(item.id, item.quantity - 1)}
                                            >
                                                −
                                            </button>
                                            <span>{item.quantity}</span>
                                            <button
                                                type="button"
                                                className="sheet-list__qty-btn"
                                                aria-label="Increase quantity"
                                                onClick={() => updateQty(item.id, item.quantity + 1)}
                                            >
                                                +
                                            </button>
                                        </div>
                                        <button
                                            type="button"
                                            className="sheet-list__remove"
                                            onClick={() => removeItem(item.id)}
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                {items.length > 0 ? (
                    <div className="side-sheet__footer">
                        <p className="side-sheet__subtotal">
                            Subtotal <strong>{subtotalLabel}</strong>
                        </p>
                        <Link href="/login" className="btn btn--primary btn--block" onClick={onClose}>
                            Checkout
                        </Link>
                    </div>
                ) : null}
            </div>
        </div>
    );
}

function CloseIcon() {
    return (
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}
