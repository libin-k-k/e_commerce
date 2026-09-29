import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function HeroBannerSection({ slides = [] }) {
    const [index, setIndex] = useState(0);
    const webSlides = slides.filter((item) => item.web_image || item.image);
    const total = webSlides.length;

    useEffect(() => {
        setIndex(0);
    }, [total]);

    useEffect(() => {
        if (total < 2) {
            return undefined;
        }

        const timer = window.setInterval(() => {
            setIndex((current) => (current + 1) % total);
        }, 5500);

        return () => window.clearInterval(timer);
    }, [total]);

    if (total === 0) {
        return null;
    }

    const slide = webSlides[index];

    return (
        <section className="hero-banner" aria-roledescription="carousel" aria-label="Featured banners">
            <div className="hero-banner__frame">
                {webSlides.map((item, i) => (
                    <article
                        key={item.id ?? item.web_image ?? item.image}
                        className={`hero-banner__slide is-${item.style ?? 'cinematic'}${i === index ? ' is-active' : ''}`}
                        aria-hidden={i !== index}
                    >
                        <img
                            className="hero-banner__image"
                            src={item.web_image || item.image}
                            alt=""
                            loading={i === 0 ? 'eager' : 'lazy'}
                        />
                        <div className="hero-banner__overlay">
                            <div className="hero-banner__copy">
                                <h2 className="hero-banner__title">{item.title}</h2>
                                {item.text ? <p className="hero-banner__text">{item.text}</p> : null}
                                <Link href={item.href ?? '/products'} className="btn btn--hero">
                                    {item.cta ?? 'Shop now'}
                                    <ArrowIcon />
                                </Link>
                            </div>
                        </div>
                    </article>
                ))}

                {total > 1 ? (
                    <>
                        <button
                            type="button"
                            className="hero-banner__arrow hero-banner__arrow--prev"
                            aria-label="Previous slide"
                            onClick={() => setIndex((current) => (current - 1 + total) % total)}
                        >
                            <ChevronIcon direction="left" />
                        </button>
                        <button
                            type="button"
                            className="hero-banner__arrow hero-banner__arrow--next"
                            aria-label="Next slide"
                            onClick={() => setIndex((current) => (current + 1) % total)}
                        >
                            <ChevronIcon direction="right" />
                        </button>
                    </>
                ) : null}

                {total > 1 ? (
                    <div className="hero-banner__dots" role="tablist" aria-label="Banner slides">
                        {webSlides.map((item, i) => (
                            <button
                                key={item.id ?? item.web_image ?? item.image}
                                type="button"
                                className={`hero-banner__dot${i === index ? ' is-active' : ''}`}
                                aria-label={`Show slide ${i + 1}`}
                                aria-selected={i === index}
                                onClick={() => setIndex(i)}
                            />
                        ))}
                    </div>
                ) : null}
            </div>

            <p className="visually-hidden">
                Slide {index + 1} of {total}: {slide.title}
            </p>
        </section>
    );
}

function ChevronIcon({ direction }) {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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

function ArrowIcon() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
