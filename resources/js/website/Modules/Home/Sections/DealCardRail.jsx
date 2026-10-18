import { Link } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

const pad = (value) => String(value).padStart(2, '0');

function useCountdown(endsAt, enabled) {
    const [remaining, setRemaining] = useState(null);

    useEffect(() => {
        if (!enabled || !endsAt) {
            setRemaining(null);

            return undefined;
        }

        const tick = () => {
            const diff = Math.floor((new Date(endsAt).getTime() - Date.now()) / 1000);
            setRemaining(Math.max(0, diff));
        };

        tick();
        const timer = window.setInterval(tick, 1000);

        return () => window.clearInterval(timer);
    }, [endsAt, enabled]);

    return remaining;
}

export default function DealCardRail({ cards = [] }) {
    const trackRef = useRef(null);
    const [atStart, setAtStart] = useState(true);
    const [atEnd, setAtEnd] = useState(false);

    useEffect(() => {
        const track = trackRef.current;

        if (!track) {
            return undefined;
        }

        const syncEdges = () => {
            const maxScroll = track.scrollWidth - track.clientWidth;
            setAtStart(track.scrollLeft <= 4);
            setAtEnd(maxScroll <= 4 || track.scrollLeft >= maxScroll - 4);
        };

        syncEdges();
        track.addEventListener('scroll', syncEdges, { passive: true });
        window.addEventListener('resize', syncEdges);

        return () => {
            track.removeEventListener('scroll', syncEdges);
            window.removeEventListener('resize', syncEdges);
        };
    }, [cards.length]);

    const scrollByPanel = (direction) => {
        const track = trackRef.current;

        if (!track) {
            return;
        }

        const panel = track.querySelector('.deal-rail__card');
        const distance = panel ? panel.getBoundingClientRect().width : track.clientWidth;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        track.scrollBy({
            left: direction * (distance + 12),
            behavior: reduceMotion ? 'auto' : 'smooth',
        });
    };

    if (cards.length === 0) {
        return null;
    }

    return (
        <section className="section deal-rail" aria-labelledby="deal-rail-title" aria-roledescription="carousel">
            <div className="section__header">
                <div className="section__heading">
                    <h2 id="deal-rail-title" className="section__title">
                        Recommended for you
                    </h2>
                </div>
                <Link href="/products" className="section__link">
                    View all products
                    <ChevronIcon />
                </Link>
            </div>

            <div className="deal-rail__scroller">
                {cards.length > 1 ? (
                    <button
                        type="button"
                        className="deal-rail__nav deal-rail__nav--prev"
                        aria-label="Previous deals"
                        disabled={atStart}
                        onClick={() => scrollByPanel(-1)}
                    >
                        <ChevronIcon direction="left" />
                    </button>
                ) : null}

                <div className="deal-rail__track" ref={trackRef} tabIndex={0} aria-label="Deal cards">
                    {cards.map((card) => (
                        <article key={card.id} className="deal-rail__card" aria-label={card.title}>
                            <div className="deal-rail__card-head">
                                <h3 className="deal-rail__card-title">{card.title}</h3>
                                <Link href={card.href || '/products'} className="deal-rail__card-link" aria-label={`View ${card.title}`}>
                                    <ChevronIcon />
                                </Link>
                            </div>
                            <CardCountdown card={card} />
                            <div className="deal-rail__grid">
                                {card.products.map((product) => (
                                    <Link key={product.slug} href={product.href} className="deal-tile">
                                        <span className="deal-tile__media">
                                            {product.image ? (
                                                <img className="deal-tile__image" src={product.image} alt="" loading="lazy" />
                                            ) : (
                                                <span className="deal-tile__image" aria-hidden="true" />
                                            )}
                                            {product.discountPercent ? (
                                                <span className="deal-tile__off">{product.discountPercent}% off</span>
                                            ) : null}
                                        </span>
                                        <span className="deal-tile__label">{product.label}</span>
                                        <span className="visually-hidden">{product.name}</span>
                                    </Link>
                                ))}
                            </div>
                        </article>
                    ))}
                </div>

                {cards.length > 1 ? (
                    <button
                        type="button"
                        className="deal-rail__nav deal-rail__nav--next"
                        aria-label="Next deals"
                        disabled={atEnd}
                        onClick={() => scrollByPanel(1)}
                    >
                        <ChevronIcon />
                    </button>
                ) : null}
            </div>
        </section>
    );
}

function CardCountdown({ card }) {
    const remaining = useCountdown(card.endsAt, card.countdownEnabled);

    if (!card.countdownEnabled || remaining === null) {
        return null;
    }

    const hours = pad(Math.floor(remaining / 3600));
    const minutes = pad(Math.floor((remaining % 3600) / 60));
    const seconds = pad(remaining % 60);

    return (
        <p className="deal-rail__timer" role="timer" aria-label="Offer ends in">
            {hours}h : {minutes}m : {seconds}s
        </p>
    );
}

function ChevronIcon({ direction = 'right' }) {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d={direction === 'left' ? 'M15 6l-6 6 6 6' : 'M9 6l6 6-6 6'}
                stroke="currentColor"
                strokeWidth="2.5"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
