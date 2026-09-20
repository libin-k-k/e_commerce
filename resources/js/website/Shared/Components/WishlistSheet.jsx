import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useId } from 'react';

export default function WishlistSheet({ open, onClose }) {
    const titleId = useId();
    const { wishlist, flash } = usePage().props;
    const items = wishlist?.items ?? [];

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

    const removeItem = (id) => {
        router.delete(`/wishlist/${id}`, { preserveScroll: true });
    };

    const addToCart = (productId) => {
        router.post(
            '/cart',
            { product_id: productId },
            {
                preserveScroll: true,
                onSuccess: () => {
                    onClose();
                },
            },
        );
    };

    return (
        <div
            className={`side-sheet${open ? ' is-open' : ''}`}
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            aria-hidden={!open}
        >
            <button
                type="button"
                className="side-sheet__backdrop"
                aria-label="Close wishlist"
                onClick={onClose}
            />
            <div className="side-sheet__panel">
                <div className="side-sheet__header">
                    <p id={titleId} className="side-sheet__title">
                        Wishlist
                    </p>
                    <button type="button" className="icon-btn" aria-label="Close wishlist" onClick={onClose}>
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
                            <p>No saved products yet.</p>
                            <button type="button" className="btn btn--primary" onClick={onClose}>
                                Browse products
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
                                        <p className="sheet-list__price">{item.price}</p>
                                        <div className="sheet-list__actions">
                                            <button
                                                type="button"
                                                className="btn btn--primary btn--compact"
                                                disabled={!item.inStock}
                                                onClick={() => addToCart(item.product_id)}
                                            >
                                                Add to cart
                                            </button>
                                            <button
                                                type="button"
                                                className="btn btn--ghost btn--compact"
                                                onClick={() => removeItem(item.id)}
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
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
