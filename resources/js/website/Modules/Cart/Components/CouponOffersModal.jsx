import { useCallback, useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import { formatMoney } from '../../Product/Sections/ProductInfoSection';

export default function CouponOffersModal({
    open,
    coupons = [],
    appliedCode = null,
    onClose,
    onApplied,
}) {
    const listRef = useRef(null);
    const [canScrollMore, setCanScrollMore] = useState(false);

    const updateScrollHint = useCallback(() => {
        const list = listRef.current;
        if (!list) {
            setCanScrollMore(false);
            return;
        }

        const remaining = list.scrollHeight - list.scrollTop - list.clientHeight;
        setCanScrollMore(list.scrollHeight > list.clientHeight + 2 && remaining > 8);
    }, []);

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.addEventListener('keydown', onKeyDown);
        document.body.classList.add('coupon-modal-locked');

        return () => {
            document.removeEventListener('keydown', onKeyDown);
            document.body.classList.remove('coupon-modal-locked');
        };
    }, [open, onClose]);

    useEffect(() => {
        if (!open) {
            setCanScrollMore(false);
            return undefined;
        }

        const list = listRef.current;
        if (!list) {
            return undefined;
        }

        updateScrollHint();
        list.addEventListener('scroll', updateScrollHint, { passive: true });
        window.addEventListener('resize', updateScrollHint);

        const observer = typeof ResizeObserver !== 'undefined' ? new ResizeObserver(updateScrollHint) : null;
        observer?.observe(list);

        return () => {
            list.removeEventListener('scroll', updateScrollHint);
            window.removeEventListener('resize', updateScrollHint);
            observer?.disconnect();
        };
    }, [open, coupons, updateScrollHint]);

    if (!open) {
        return null;
    }

    const apply = (code) => {
        router.post(
            '/cart/coupon',
            { code },
            {
                preserveScroll: true,
                onSuccess: () => {
                    onApplied?.();
                    onClose();
                },
            },
        );
    };

    const scrollListDown = () => {
        const list = listRef.current;
        if (!list) {
            return;
        }

        list.scrollBy({ top: Math.max(96, list.clientHeight * 0.55), behavior: 'smooth' });
    };

    return (
        <div className="coupon-modal is-open" role="dialog" aria-modal="true" aria-labelledby="coupon-modal-title">
            <button type="button" className="coupon-modal__backdrop" aria-label="Close coupons" onClick={onClose} />

            <div className="coupon-modal__panel">
                <div className="coupon-modal__hero">
                    <span className="coupon-modal__gift" aria-hidden="true">
                        <GiftMark />
                    </span>
                    <h2 id="coupon-modal-title" className="coupon-modal__title">
                        Coupon Gift
                    </h2>
                    <p className="coupon-modal__subtitle">Start saving now - only one coupon at a time</p>
                </div>

                <div className={`coupon-modal__list-shell${canScrollMore ? ' has-more' : ''}`}>
                    <div ref={listRef} className="coupon-modal__list">
                        {coupons.length === 0 ? (
                            <p className="coupon-modal__empty">No coupons available right now.</p>
                        ) : (
                            coupons.map((coupon) => {
                                const isApplied = Boolean(coupon.applied || (appliedCode && appliedCode === coupon.code));
                                const canUse = coupon.applicable && !isApplied;

                                return (
                                    <article
                                        key={coupon.code}
                                        className={`coupon-ticket${isApplied ? ' is-applied' : ''}${!coupon.applicable ? ' is-disabled' : ''}`}
                                    >
                                        <div className="coupon-ticket__value">
                                            <strong>{coupon.headline}</strong>
                                        </div>
                                        <div className="coupon-ticket__body">
                                            <div className="coupon-ticket__copy">
                                                <p className="coupon-ticket__name">{coupon.name || 'Coupon'}</p>
                                                <p className="coupon-ticket__meta">
                                                    {coupon.code} · {coupon.expires_label}
                                                </p>
                                                {coupon.applicable && coupon.discount > 0 ? (
                                                    <p className="coupon-ticket__save">Save {formatMoney(coupon.discount)}</p>
                                                ) : null}
                                                {!coupon.applicable && coupon.reason ? (
                                                    <p className="coupon-ticket__reason">{coupon.reason}</p>
                                                ) : null}
                                            </div>
                                            <button
                                                type="button"
                                                className="coupon-ticket__btn"
                                                disabled={!canUse}
                                                onClick={() => apply(coupon.code)}
                                            >
                                                {isApplied ? 'Applied' : 'Use it now'}
                                            </button>
                                        </div>
                                    </article>
                                );
                            })
                        )}
                    </div>

                    {canScrollMore ? (
                        <button
                            type="button"
                            className="coupon-modal__scroll-hint"
                            aria-label="Scroll for more coupons"
                            onClick={scrollListDown}
                        >
                            <ChevronDownMark />
                        </button>
                    ) : null}
                </div>

                <p className="coupon-modal__footer">Enjoy more coupons on your next order!</p>
            </div>

            <button type="button" className="coupon-modal__close" aria-label="Close" onClick={onClose}>
                <CloseMark />
            </button>
        </div>
    );
}

function GiftMark() {
    return (
        <svg width="56" height="56" viewBox="0 0 56 56" fill="none" aria-hidden="true">
            <rect x="10" y="24" width="36" height="24" rx="6" fill="currentColor" opacity="0.95" />
            <rect x="8" y="18" width="40" height="10" rx="4" fill="currentColor" />
            <rect x="25" y="18" width="6" height="30" fill="#fff" opacity="0.85" />
            <path
                d="M28 18c-4-8-12-8-12-2 0 4 5 6 12 8 7-2 12-4 12-8 0-6-8-6-12 2z"
                fill="#FFD666"
            />
        </svg>
    );
}

function CloseMark() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
        </svg>
    );
}

function ChevronDownMark() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 9l6 6 6-6" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
