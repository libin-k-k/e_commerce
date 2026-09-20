import { useState } from 'react';

export default function ProductGallerySection({ images = [], name = '' }) {
    const gallery = images.length > 0 ? images : [];
    const [active, setActive] = useState(0);
    const current = gallery[active] ?? gallery[0];

    if (!current) {
        return null;
    }

    return (
        <section className="pdp-gallery" aria-label="Product images">
            <div className="pdp-gallery__main">
                <img src={current} alt={name} className="pdp-gallery__image" />
            </div>
            {gallery.length > 1 ? (
                <div className="pdp-gallery__thumbs" role="list">
                    {gallery.map((src, index) => (
                        <button
                            key={src}
                            type="button"
                            className={`pdp-gallery__thumb${index === active ? ' is-active' : ''}`}
                            onClick={() => setActive(index)}
                            aria-label={`View image ${index + 1}`}
                            aria-pressed={index === active}
                        >
                            <img src={src} alt="" loading="lazy" />
                        </button>
                    ))}
                </div>
            ) : null}
        </section>
    );
}
