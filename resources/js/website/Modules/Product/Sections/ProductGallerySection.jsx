import { useRef, useState } from 'react';

const SWIPE_DISTANCE = 40;

export default function ProductGallerySection({ images = [], name = '', badge = null, wished = false, onToggleWishlist }) {
    const [active, setActive] = useState(0);
    const touchStartX = useRef(null);
    const count = images.length;

    if (count === 0) {
        return null;
    }

    const index = Math.min(active, count - 1);
    const go = (step) => setActive((current) => (current + step + count) % count);

    const onTouchStart = (event) => {
        touchStartX.current = event.touches[0].clientX;
    };

    const onTouchEnd = (event) => {
        if (touchStartX.current === null || count < 2) {
            return;
        }

        const distance = event.changedTouches[0].clientX - touchStartX.current;
        touchStartX.current = null;

        if (Math.abs(distance) >= SWIPE_DISTANCE) {
            go(distance < 0 ? 1 : -1);
        }
    };

    return (
        <section className="pdp-gallery" aria-label="Product images">
            <div className="pdp-gallery__stage" onTouchStart={onTouchStart} onTouchEnd={onTouchEnd}>
                <img
                    src={images[index]}
                    alt={count > 1 ? `${name} - image ${index + 1} of ${count}` : name}
                    className="pdp-gallery__image"
                />
                {badge ? <span className={`pdp-gallery__badge${badge.isNew ? ' is-new' : ''}`}>{badge.label}</span> : null}
                <button
                    type="button"
                    className={`pdp-gallery__wishlist${wished ? ' is-active' : ''}`}
                    aria-label={wished ? 'Remove from wishlist' : 'Add to wishlist'}
                    aria-pressed={wished}
                    onClick={onToggleWishlist}
                >
                    <HeartIcon filled={wished} />
                </button>
                {count > 1 ? (
                    <>
                        <button
                            type="button"
                            className="pdp-gallery__arrow pdp-gallery__arrow--prev"
                            aria-label="Previous image"
                            onClick={() => go(-1)}
                        >
                            <ChevronIcon direction="left" />
                        </button>
                        <button
                            type="button"
                            className="pdp-gallery__arrow pdp-gallery__arrow--next"
                            aria-label="Next image"
                            onClick={() => go(1)}
                        >
                            <ChevronIcon direction="right" />
                        </button>
                        <span className="pdp-gallery__counter" aria-hidden="true">
                            {index + 1}/{count}
                        </span>
                    </>
                ) : null}
            </div>

            {count > 1 ? (
                <ul className="pdp-gallery__thumbs">
                    {images.map((src, thumbIndex) => (
                        <li key={src}>
                            <button
                                type="button"
                                className={`pdp-gallery__thumb${thumbIndex === index ? ' is-active' : ''}`}
                                onClick={() => setActive(thumbIndex)}
                                aria-label={`View image ${thumbIndex + 1}`}
                                aria-pressed={thumbIndex === index}
                            >
                                <img src={src} alt="" loading="lazy" />
                            </button>
                        </li>
                    ))}
                </ul>
            ) : null}
        </section>
    );
}

function HeartIcon({ filled }) {
    return (
        <svg width="20" height="20" viewBox="0 0 24 24" fill={filled ? 'currentColor' : 'none'} aria-hidden="true">
            <path
                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function ChevronIcon({ direction }) {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d={direction === 'left' ? 'M15 6l-6 6 6 6' : 'M9 6l6 6-6 6'}
                stroke="currentColor"
                strokeWidth="2.4"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
