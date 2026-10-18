const SPARK_COUNT = 12;

export default function CouponFireworks({ active = false }) {
    if (!active) {
        return null;
    }

    return (
        <div className="coupon-fireworks is-active" aria-hidden="true">
            <div className="coupon-fireworks__burst coupon-fireworks__burst--a" />
            <div className="coupon-fireworks__burst coupon-fireworks__burst--b" />
            <div className="coupon-fireworks__burst coupon-fireworks__burst--c" />
            {Array.from({ length: SPARK_COUNT }, (_, index) => (
                <span
                    key={`near-${index}`}
                    className={`coupon-fireworks__spark coupon-fireworks__spark--near coupon-fireworks__spark--${index + 1}`}
                />
            ))}
            {Array.from({ length: SPARK_COUNT }, (_, index) => (
                <span
                    key={`far-${index}`}
                    className={`coupon-fireworks__spark coupon-fireworks__spark--far coupon-fireworks__spark--${index + 1}`}
                />
            ))}
            <span className="coupon-fireworks__ring coupon-fireworks__ring--1" />
            <span className="coupon-fireworks__ring coupon-fireworks__ring--2" />
            <p className="coupon-fireworks__toast">Coupon applied</p>
        </div>
    );
}
