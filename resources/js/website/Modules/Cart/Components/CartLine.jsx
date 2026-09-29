import { Link } from '@inertiajs/react';
import { formatMoney } from '../../Product/Sections/ProductInfoSection';
import { CheckIcon, HeartIcon, TrashIcon } from './CartIcons';

export default function CartLine({
    item,
    selected,
    wished,
    pending,
    onToggleSelected,
    onQuantityChange,
    onRemove,
    onMoveToWishlist,
    onToggleWishlist,
}) {
    const href = `/products/${item.slug}`;
    const saving = item.line_mrp - item.line_total;
    const atMax = item.quantity >= item.max_quantity;

    return (
        <li className={`cart-line${selected ? '' : ' is-unselected'}`}>
            <label className="cart-check cart-line__check">
                <input type="checkbox" checked={selected} onChange={onToggleSelected} />
                <span className="cart-check__box" aria-hidden="true">
                    <CheckIcon />
                </span>
                <span className="visually-hidden">Select {item.name}</span>
            </label>

            <Link href={href} className="cart-line__media" tabIndex={-1} aria-hidden="true">
                {item.image ? <img src={item.image} alt="" loading="lazy" /> : null}
            </Link>

            <div className="cart-line__info">
                <Link href={href} className="cart-line__name">
                    {item.name}
                </Link>
                {item.category ? <p className="cart-line__category">{item.category}</p> : null}
                {item.size || item.color || !item.in_stock ? (
                    <ul className="cart-line__chips">
                        {item.size ? <li>Size: {item.size}</li> : null}
                        {item.color ? <li>Color: {item.color}</li> : null}
                        {!item.in_stock ? <li className="cart-line__chip--alert">Out of stock</li> : null}
                    </ul>
                ) : null}
            </div>

            <div className="cart-line__qty" role="group" aria-label={`Quantity for ${item.name}`}>
                <button
                    type="button"
                    className="cart-line__qty-btn"
                    aria-label={item.quantity > 1 ? 'Decrease quantity' : `Remove ${item.name}`}
                    disabled={pending}
                    onClick={() => (item.quantity > 1 ? onQuantityChange(item.quantity - 1) : onRemove())}
                >
                    {item.quantity > 1 ? <MinusIcon /> : <TrashIcon size={15} />}
                </button>
                <span className="cart-line__qty-value" aria-live="polite">
                    {item.quantity}
                </span>
                <button
                    type="button"
                    className="cart-line__qty-btn"
                    aria-label="Increase quantity"
                    title={atMax ? 'Maximum available quantity' : undefined}
                    disabled={pending || atMax}
                    onClick={() => onQuantityChange(item.quantity + 1)}
                >
                    <PlusIcon />
                </button>
            </div>

            <div className="cart-line__actions">
                <button type="button" className="cart-line__action" disabled={pending} onClick={onMoveToWishlist}>
                    <HeartIcon />
                    Move to Wishlist
                </button>
                <button type="button" className="cart-line__action" disabled={pending} onClick={onRemove}>
                    <TrashIcon />
                    Remove
                </button>
            </div>

            <div className="cart-line__side">
                <button
                    type="button"
                    className={`cart-line__wish${wished ? ' is-active' : ''}`}
                    aria-label={wished ? `Remove ${item.name} from wishlist` : `Add ${item.name} to wishlist`}
                    aria-pressed={wished}
                    onClick={onToggleWishlist}
                >
                    <HeartIcon filled={wished} size={20} />
                </button>
                <div className="cart-line__price">
                    <strong>{formatMoney(item.line_total)}</strong>
                    {saving > 0 ? (
                        <>
                            <s>{formatMoney(item.line_mrp)}</s>
                            <span className="cart-line__save">
                                Save {formatMoney(saving)}
                                <span className="cart-line__save-pct"> ({item.discount_percent}%)</span>
                            </span>
                        </>
                    ) : null}
                    {item.quantity > 1 ? <span className="cart-line__each">{formatMoney(item.unit_price)} each</span> : null}
                </div>
            </div>
        </li>
    );
}

function MinusIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
        </svg>
    );
}

function PlusIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 5v14M5 12h14" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
        </svg>
    );
}
