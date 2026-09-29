import { Link, usePage } from '@inertiajs/react';
import { formatMoney } from '../../Product/Sections/ProductInfoSection';
import { ArrowIcon, CheckCircleIcon, InfoIcon, ReturnIcon, ShieldIcon, TruckIcon } from './CartIcons';

const itemsLabel = (count) => `${count} ${count === 1 ? 'item' : 'items'}`;

/**
 * Guests sign in first; there is no online checkout for signed-in shoppers yet.
 */
export function CheckoutButton({ className = '', disabled = false }) {
    const { auth } = usePage().props;
    const classes = `cart-checkout-btn ${className}`.trim();

    if (!auth?.user && !disabled) {
        return (
            <Link href="/login" className={classes}>
                Proceed to Checkout
                <ArrowIcon />
            </Link>
        );
    }

    return (
        <button
            type="button"
            className={classes}
            disabled
            title={disabled ? 'Select at least one item' : 'Online checkout is coming soon'}
        >
            Proceed to Checkout
            <ArrowIcon />
        </button>
    );
}

export default function CartSummary({ summary, freeAbove }) {
    const { auth } = usePage().props;

    return (
        <aside className="cart-summary" aria-labelledby="cart-summary-title">
            <h2 id="cart-summary-title" className="cart-summary__title">
                Order Summary
            </h2>

            <dl className="cart-summary__rows">
                <div>
                    <dt>Total MRP ({itemsLabel(summary.units)})</dt>
                    <dd>{formatMoney(summary.mrp)}</dd>
                </div>
                {summary.discount > 0 ? (
                    <div className="cart-summary__row--saving">
                        <dt>Item Discount</dt>
                        <dd>- {formatMoney(summary.discount)}</dd>
                    </div>
                ) : null}
                <div className={summary.deliveryFee === 0 && summary.units > 0 ? 'cart-summary__row--saving' : ''}>
                    <dt>
                        Delivery Charges
                        <span className="cart-summary__hint" title={`Free delivery on orders of ${formatMoney(freeAbove)} or more`}>
                            <InfoIcon />
                            <span className="visually-hidden">Free delivery on orders of {formatMoney(freeAbove)} or more</span>
                        </span>
                    </dt>
                    <dd>{summary.deliveryFee === 0 ? (summary.units > 0 ? 'FREE' : formatMoney(0)) : formatMoney(summary.deliveryFee)}</dd>
                </div>
            </dl>

            <div className="cart-summary__total">
                <span>Total Amount</span>
                <strong>{formatMoney(summary.total)}</strong>
            </div>
            {summary.discount > 0 ? (
                <p className="cart-summary__saving">
                    <CheckCircleIcon />
                    You are saving {formatMoney(summary.discount)}
                </p>
            ) : null}

            <CheckoutButton className="cart-summary__checkout" disabled={summary.units === 0} />
            {auth?.user ? <p className="cart-summary__note">Online checkout is coming soon.</p> : null}

            <ul className="cart-summary__trust">
                <li>
                    <ShieldIcon />
                    <span>
                        <strong>Secure Payment</strong>
                        100% safe
                    </span>
                </li>
                <li>
                    <ReturnIcon />
                    <span>
                        <strong>Easy Returns</strong>
                        7 days policy
                    </span>
                </li>
                <li>
                    <TruckIcon size={20} />
                    <span>
                        <strong>Free Delivery</strong>
                        on {formatMoney(freeAbove).replace('.00', '')}+ orders
                    </span>
                </li>
            </ul>
        </aside>
    );
}
