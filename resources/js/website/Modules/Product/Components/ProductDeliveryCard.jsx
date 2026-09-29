import { Link, usePage } from '@inertiajs/react';

export default function ProductDeliveryCard({ address }) {
    const { auth } = usePage().props;
    const signedIn = Boolean(auth?.user);

    return (
        <section className="pdp-card pdp-delivery" aria-labelledby="pdp-delivery-title">
            <h2 id="pdp-delivery-title" className="pdp-card__title">
                <TruckIcon />
                Delivery Details
            </h2>

            <div className="pdp-delivery__address">
                <div className="pdp-delivery__address-copy">
                    {address ? (
                        <>
                            <p>
                                Deliver to: <strong>{address.name}</strong>
                            </p>
                            <p className="pdp-delivery__line">
                                {address.label} · {address.line}
                            </p>
                        </>
                    ) : (
                        <p className="pdp-delivery__line">
                            {signedIn ? 'Add an address to see where we will deliver.' : 'Sign in to deliver to your saved address.'}
                        </p>
                    )}
                </div>
                <Link href={signedIn ? '/account/addresses' : '/login'} className="pdp-delivery__change">
                    {address ? 'Change' : signedIn ? 'Add' : 'Sign in'}
                </Link>
            </div>

            <ul className="pdp-delivery__list">
                <li className="pdp-delivery__item">
                    <TruckIcon />
                    <div>
                        <p className="pdp-delivery__item-title">Standard Delivery</p>
                        <p className="pdp-delivery__item-text">Ships within 24–48 hours · arrives in 2–7 days</p>
                    </div>
                </li>
                <li className="pdp-delivery__item">
                    <ReturnIcon />
                    <div>
                        <p className="pdp-delivery__item-title">7-Day Returns</p>
                        <p className="pdp-delivery__item-text">Unused items with tags and packaging intact</p>
                    </div>
                </li>
            </ul>
        </section>
    );
}

function TruckIcon() {
    return (
        <svg className="pdp-card__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M3 6h11v9H3zM14 9h4l3 3v3h-7z" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" />
            <circle cx="7" cy="17.5" r="1.8" stroke="currentColor" strokeWidth="1.8" />
            <circle cx="17" cy="17.5" r="1.8" stroke="currentColor" strokeWidth="1.8" />
        </svg>
    );
}

function ReturnIcon() {
    return (
        <svg className="pdp-card__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 9h11a5 5 0 0 1 0 10h-4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
            <path d="M8 5L4 9l4 4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
