import { Link, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import ProductCard from '../../../Shared/Components/ProductCard';
import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';
import CartHeader from '../Components/CartHeader';
import { ArrowIcon, BagIcon, CheckIcon, HeartIcon, TrashIcon } from '../Components/CartIcons';
import CartLine from '../Components/CartLine';
import CartSummary, { CheckoutButton } from '../Components/CartSummary';
import DeliveryProgress from '../Components/DeliveryProgress';
import { formatMoney } from '../../Product/Sections/ProductInfoSection';

const roundMoney = (amount) => Math.round(amount * 100) / 100;

const itemsLabel = (count) => `${count} ${count === 1 ? 'item' : 'items'}`;

export default function Index({ seo, delivery, recommendations = [] }) {
    const { cart, cartCount = 0, wishlistProductIds = [], flash } = usePage().props;
    const items = cart?.items ?? [];
    const [deselected, setDeselected] = useState(() => new Set());
    const [pending, setPending] = useState(false);

    const selectedItems = items.filter((item) => !deselected.has(item.id));
    const selectedIds = selectedItems.map((item) => item.id);
    const allSelected = items.length > 0 && selectedItems.length === items.length;

    const summary = useMemo(() => {
        const units = selectedItems.reduce((total, item) => total + item.quantity, 0);
        const mrp = roundMoney(selectedItems.reduce((total, item) => total + item.line_mrp, 0));
        const subtotal = roundMoney(selectedItems.reduce((total, item) => total + item.line_total, 0));
        const deliveryFee = subtotal === 0 || subtotal >= delivery.freeAbove ? 0 : delivery.fee;

        return {
            units,
            mrp,
            subtotal,
            discount: roundMoney(mrp - subtotal),
            deliveryFee,
            total: roundMoney(subtotal + deliveryFee),
            remaining: roundMoney(Math.max(0, delivery.freeAbove - subtotal)),
        };
    }, [selectedItems, delivery]);

    const visitOptions = {
        preserveScroll: true,
        onStart: () => setPending(true),
        onFinish: () => setPending(false),
    };

    const toggleItem = (id) => {
        setDeselected((current) => {
            const next = new Set(current);

            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }

            return next;
        });
    };

    const toggleAll = () => {
        setDeselected(allSelected ? new Set(items.map((item) => item.id)) : new Set());
    };

    const updateQuantity = (id, quantity) => router.patch(`/cart/${id}`, { quantity }, visitOptions);

    const removeItem = (id) => router.delete(`/cart/${id}`, visitOptions);

    const removeMany = (ids) => router.delete('/cart', { ...visitOptions, data: { items: ids } });

    const moveToWishlist = (ids) => router.post('/cart/wishlist', { items: ids }, visitOptions);

    const toggleWishlist = (productId) =>
        router.post('/wishlist', { product_id: productId }, { preserveScroll: true });

    const clearCart = () => {
        if (items.length > 0 && window.confirm('Remove every item from your cart?')) {
            removeMany(items.map((item) => item.id));
        }
    };

    const hasItems = items.length > 0;

    return (
        <StorefrontLayout seo={seo} current="cart" hideHeaderOnMobile>
            <CartHeader
                count={cartCount}
                allSelected={allSelected}
                hasItems={hasItems}
                onToggleAll={toggleAll}
                onClearCart={clearCart}
            />

            <div className={`cart-page${hasItems ? ' has-checkout-bar' : ''}`}>
                <nav className="listing-breadcrumb cart-page__breadcrumb" aria-label="Breadcrumb">
                    <ol>
                        <li>
                            <Link href="/">Home</Link>
                        </li>
                        <li aria-current="page">My Cart</li>
                    </ol>
                </nav>

                <div className="cart-page__head">
                    <div>
                        <h1 className="cart-page__title">
                            My Cart <span>({itemsLabel(cartCount)})</span>
                        </h1>
                        <p className="cart-page__subtitle">Review your items before checkout</p>
                    </div>
                    {hasItems ? (
                        <button type="button" className="cart-page__clear" onClick={clearCart} disabled={pending}>
                            <TrashIcon />
                            Clear Cart
                        </button>
                    ) : null}
                </div>

                {hasItems ? (
                    <div className="cart-page__layout">
                        <DeliveryProgress subtotal={summary.subtotal} freeAbove={delivery.freeAbove} remaining={summary.remaining} />

                        <section className="cart-items" aria-label="Items in your cart">
                            <ul className="cart-items__list">
                                {items.map((item) => (
                                    <CartLine
                                        key={item.id}
                                        item={item}
                                        selected={!deselected.has(item.id)}
                                        wished={wishlistProductIds.includes(item.product_id)}
                                        pending={pending}
                                        onToggleSelected={() => toggleItem(item.id)}
                                        onQuantityChange={(quantity) => updateQuantity(item.id, quantity)}
                                        onRemove={() => removeItem(item.id)}
                                        onMoveToWishlist={() => moveToWishlist([item.id])}
                                        onToggleWishlist={() => toggleWishlist(item.product_id)}
                                    />
                                ))}
                            </ul>

                            <div className="cart-items__footer">
                                <label className="cart-check cart-items__select-all">
                                    <input type="checkbox" checked={allSelected} onChange={toggleAll} />
                                    <span className="cart-check__box" aria-hidden="true">
                                        <CheckIcon />
                                    </span>
                                    <span className="cart-items__select-label">
                                        Select All <span>({itemsLabel(items.length)})</span>
                                    </span>
                                </label>
                                <div className="cart-items__bulk">
                                    <button
                                        type="button"
                                        className="cart-items__bulk-btn"
                                        disabled={pending || selectedIds.length === 0}
                                        onClick={() => removeMany(selectedIds)}
                                    >
                                        <TrashIcon />
                                        Remove Selected ({selectedIds.length})
                                    </button>
                                    <button
                                        type="button"
                                        className="cart-items__bulk-btn"
                                        disabled={pending || selectedIds.length === 0}
                                        onClick={() => moveToWishlist(selectedIds)}
                                    >
                                        <HeartIcon />
                                        <span className="cart-items__bulk-long">Move Selected to Wishlist</span>
                                        <span className="cart-items__bulk-short">Move to Wishlist</span>
                                    </button>
                                </div>
                            </div>
                        </section>

                        <CartSummary summary={summary} freeAbove={delivery.freeAbove} />
                    </div>
                ) : (
                    <div className="cart-empty">
                        <span className="cart-empty__icon" aria-hidden="true">
                            <BagIcon />
                        </span>
                        <h2 className="cart-empty__title">Your cart is empty</h2>
                        <p className="cart-empty__text">Looks like you haven&apos;t added anything yet.</p>
                        <Link href="/products" className="btn btn--primary cart-empty__cta">
                            Start Shopping
                        </Link>
                    </div>
                )}

                {recommendations.length > 0 ? (
                    <section className="cart-recs" aria-labelledby="cart-recs-title">
                        <div className="cart-recs__header">
                            <h2 id="cart-recs-title" className="cart-recs__title">
                                You May Also Like
                            </h2>
                            <Link href="/products" className="cart-recs__all">
                                See All
                                <ArrowIcon />
                            </Link>
                        </div>
                        <div className="product-grid cart-recs__grid">
                            {recommendations.map((product) => (
                                <ProductCard key={product.slug} product={product} titleAs="h3" />
                            ))}
                        </div>
                    </section>
                ) : null}

                {flash?.success ? (
                    <p className="visually-hidden" role="status">
                        {flash.success}
                    </p>
                ) : null}
            </div>

            {hasItems ? (
                <div className="cart-checkout-bar">
                    <div className="cart-checkout-bar__total">
                        <span>{itemsLabel(summary.units)}</span>
                        <strong>{formatMoney(summary.total)}</strong>
                    </div>
                    <CheckoutButton className="cart-checkout-bar__btn" disabled={selectedIds.length === 0} />
                </div>
            ) : null}
        </StorefrontLayout>
    );
}
