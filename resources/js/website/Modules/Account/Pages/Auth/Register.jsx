import { Head, Link, useForm, usePage } from '@inertiajs/react';
import StorefrontLayout from '../../../../Shared/Layouts/StorefrontLayout';

const emptyAddress = () => ({
    label: 'Home',
    full_address: '',
    pincode: '',
    district: '',
    state: '',
    is_default: true,
});

export default function Register({ seo, addressLabels = ['Home', 'Work', 'Other'] }) {
    const { appName } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        mobile: '',
        password: '',
        password_confirmation: '',
        addresses: [emptyAddress()],
    });

    const updateAddress = (index, key, value) => {
        setData(
            'addresses',
            data.addresses.map((address, addressIndex) => {
                if (addressIndex !== index) {
                    if (key === 'is_default' && value) {
                        return { ...address, is_default: false };
                    }

                    return address;
                }

                return { ...address, [key]: value };
            }),
        );
    };

    const addAddress = () => {
        setData('addresses', [
            ...data.addresses,
            {
                ...emptyAddress(),
                label: 'Work',
                is_default: false,
            },
        ]);
    };

    const removeAddress = (index) => {
        if (data.addresses.length <= 1) {
            return;
        }

        const next = data.addresses.filter((_, addressIndex) => addressIndex !== index);
        if (! next.some((address) => address.is_default)) {
            next[0] = { ...next[0], is_default: true };
        }
        setData('addresses', next);
    };

    const onSubmit = (event) => {
        event.preventDefault();
        post('/register');
    };

    return (
        <StorefrontLayout seo={seo} current="account">
            <Head title={seo?.title} />
            <section className="auth-page">
                <div className="auth-page__card auth-page__card--wide">
                    <p className="auth-page__brand">{appName}</p>
                    <h1 className="auth-page__title">Create account</h1>
                    <p className="auth-page__text">
                        Mobile is required. Email is optional. Add one or more delivery addresses.
                    </p>
                    <form className="auth-page__form" onSubmit={onSubmit}>
                        <label className="auth-page__label" htmlFor="register-name">
                            Name
                            <input
                                id="register-name"
                                className="auth-page__input"
                                type="text"
                                value={data.name}
                                onChange={(event) => setData('name', event.target.value)}
                                required
                                autoComplete="name"
                            />
                        </label>
                        {errors.name ? <p className="auth-page__error">{errors.name}</p> : null}

                        <label className="auth-page__label" htmlFor="register-mobile">
                            Mobile
                            <input
                                id="register-mobile"
                                className="auth-page__input"
                                type="tel"
                                value={data.mobile}
                                onChange={(event) => setData('mobile', event.target.value)}
                                required
                                autoComplete="tel"
                                inputMode="numeric"
                                placeholder="10-digit mobile"
                            />
                        </label>
                        {errors.mobile ? <p className="auth-page__error">{errors.mobile}</p> : null}

                        <label className="auth-page__label" htmlFor="register-email">
                            Email <span className="auth-page__optional">(optional)</span>
                            <input
                                id="register-email"
                                className="auth-page__input"
                                type="email"
                                value={data.email}
                                onChange={(event) => setData('email', event.target.value)}
                                autoComplete="email"
                            />
                        </label>
                        {errors.email ? <p className="auth-page__error">{errors.email}</p> : null}

                        <label className="auth-page__label" htmlFor="register-password">
                            Password
                            <input
                                id="register-password"
                                className="auth-page__input"
                                type="password"
                                value={data.password}
                                onChange={(event) => setData('password', event.target.value)}
                                required
                                autoComplete="new-password"
                            />
                        </label>
                        {errors.password ? <p className="auth-page__error">{errors.password}</p> : null}

                        <label className="auth-page__label" htmlFor="register-password-confirm">
                            Confirm password
                            <input
                                id="register-password-confirm"
                                className="auth-page__input"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(event) => setData('password_confirmation', event.target.value)}
                                required
                                autoComplete="new-password"
                            />
                        </label>

                        <div className="auth-address-block">
                            <div className="auth-address-block__head">
                                <h2 className="auth-address-block__title">Addresses</h2>
                                <button type="button" className="btn btn--ghost btn--sm" onClick={addAddress}>
                                    Add another
                                </button>
                            </div>
                            {errors.addresses ? <p className="auth-page__error">{errors.addresses}</p> : null}

                            {data.addresses.map((address, index) => (
                                <fieldset key={index} className="auth-address">
                                    <legend className="auth-address__legend">Address {index + 1}</legend>

                                    <label className="auth-page__label" htmlFor={`address-label-${index}`}>
                                        Label
                                        <select
                                            id={`address-label-${index}`}
                                            className="auth-page__input"
                                            value={address.label}
                                            onChange={(event) => updateAddress(index, 'label', event.target.value)}
                                        >
                                            {addressLabels.map((label) => (
                                                <option key={label} value={label}>
                                                    {label}
                                                </option>
                                            ))}
                                            {! addressLabels.includes(address.label) ? (
                                                <option value={address.label}>{address.label}</option>
                                            ) : null}
                                        </select>
                                    </label>
                                    {errors[`addresses.${index}.label`] ? (
                                        <p className="auth-page__error">{errors[`addresses.${index}.label`]}</p>
                                    ) : null}

                                    <label className="auth-page__label" htmlFor={`address-full-${index}`}>
                                        Full address
                                        <textarea
                                            id={`address-full-${index}`}
                                            className="auth-page__textarea"
                                            rows={3}
                                            value={address.full_address}
                                            onChange={(event) => updateAddress(index, 'full_address', event.target.value)}
                                            required
                                        />
                                    </label>
                                    {errors[`addresses.${index}.full_address`] ? (
                                        <p className="auth-page__error">{errors[`addresses.${index}.full_address`]}</p>
                                    ) : null}

                                    <div className="auth-address__grid">
                                        <label className="auth-page__label" htmlFor={`address-pincode-${index}`}>
                                            Pincode
                                            <input
                                                id={`address-pincode-${index}`}
                                                className="auth-page__input"
                                                type="text"
                                                value={address.pincode}
                                                onChange={(event) => updateAddress(index, 'pincode', event.target.value)}
                                                required
                                                inputMode="numeric"
                                            />
                                        </label>
                                        <label className="auth-page__label" htmlFor={`address-district-${index}`}>
                                            District
                                            <input
                                                id={`address-district-${index}`}
                                                className="auth-page__input"
                                                type="text"
                                                value={address.district}
                                                onChange={(event) => updateAddress(index, 'district', event.target.value)}
                                                required
                                            />
                                        </label>
                                        <label className="auth-page__label" htmlFor={`address-state-${index}`}>
                                            State
                                            <input
                                                id={`address-state-${index}`}
                                                className="auth-page__input"
                                                type="text"
                                                value={address.state}
                                                onChange={(event) => updateAddress(index, 'state', event.target.value)}
                                                required
                                            />
                                        </label>
                                    </div>
                                    {(errors[`addresses.${index}.pincode`]
                                        || errors[`addresses.${index}.district`]
                                        || errors[`addresses.${index}.state`]) ? (
                                        <p className="auth-page__error">
                                            {errors[`addresses.${index}.pincode`]
                                                || errors[`addresses.${index}.district`]
                                                || errors[`addresses.${index}.state`]}
                                        </p>
                                    ) : null}

                                    <label className="auth-page__check">
                                        <input
                                            type="radio"
                                            name="default_address"
                                            checked={Boolean(address.is_default)}
                                            onChange={() => updateAddress(index, 'is_default', true)}
                                        />
                                        Default address
                                    </label>

                                    {data.addresses.length > 1 ? (
                                        <button
                                            type="button"
                                            className="btn btn--ghost btn--sm"
                                            onClick={() => removeAddress(index)}
                                        >
                                            Remove address
                                        </button>
                                    ) : null}
                                </fieldset>
                            ))}
                        </div>

                        <button type="submit" className="btn btn--primary btn--block" disabled={processing}>
                            {processing ? 'Creating...' : 'Create account'}
                        </button>
                    </form>
                    <p className="auth-page__footer">
                        Already have an account? <Link href="/login">Sign in</Link>
                    </p>
                </div>
            </section>
        </StorefrontLayout>
    );
}
