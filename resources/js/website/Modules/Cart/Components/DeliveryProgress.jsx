import { formatMoney } from '../../Product/Sections/ProductInfoSection';
import { TruckIcon } from './CartIcons';

export default function DeliveryProgress({ subtotal, freeAbove, remaining }) {
    if (freeAbove <= 0) {
        return null;
    }

    const unlocked = remaining <= 0;
    const progress = Math.min(100, Math.round((subtotal / freeAbove) * 100));

    return (
        <div className={`cart-delivery${unlocked ? ' is-unlocked' : ''}`}>
            <span className="cart-delivery__icon" aria-hidden="true">
                <TruckIcon />
            </span>
            <div className="cart-delivery__body">
                <p className="cart-delivery__text">
                    {unlocked ? (
                        <>
                            Yay! You get <strong>FREE delivery</strong> on this order
                        </>
                    ) : (
                        <>
                            Add <strong>{formatMoney(remaining)}</strong> more to get FREE delivery!
                        </>
                    )}
                </p>
                <div
                    className="cart-delivery__track"
                    role="progressbar"
                    aria-label="Progress towards free delivery"
                    aria-valuemin={0}
                    aria-valuemax={100}
                    aria-valuenow={progress}
                >
                    <span style={{ width: `${progress}%` }} />
                </div>
            </div>
            {!unlocked ? <span className="cart-delivery__togo">{formatMoney(remaining)} to go</span> : null}
        </div>
    );
}
