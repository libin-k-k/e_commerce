import { Head, useForm } from '@inertiajs/react';
import BrandLogo from '../../../../Shared/Components/BrandLogo';
import StorefrontLayout from '../../../../Shared/Layouts/StorefrontLayout';

const emptyAddress = () => ({
    label: 'Home',
    full_address: '',
    pincode: '',
    district: '',
    state: '',
    is_default: true,
});

export default function CompleteGoogleRegistration({
    seo,
    profile,
    addressLabels = ['Home', 'Work', 'Other'],
}) {
    const { data, setData, post, processing, errors } = useForm({
        name: profile?.name ?? '',
        mobile: '',
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
        post('/register/google/complete');
    };

    return (
        <StorefrontLayout seo={seo} current="account">
            <Head title={seo?.title} />
            <section className="auth-page">
                <div className="auth-page__card auth-page__card--wide">
                    <BrandLogo className="auth-page__logo" />
                    <h1 className="auth-page__title">Finish Google registration</h1>
                    <p className="auth-page__text">
                        Add your mobile number and at least one delivery address to complete your
                        account. Registration is not finished until this step is saved.
                    </p>
                    {profile?.email ? (
                        <p className="auth-page__google-email">Signed in with Google as {profile.email}</p>
                    ) : null}

                    <form className="auth-page__form" onSubmit={onSubmit}>
                        <label className="auth-page__label" htmlFor="google-register-name">
                            Name
                            <input
                                id="google-register-name"
                                className="auth-page__input"
                                type="text"
                                value={data.name}
                                onChange={(event) => setData('name', event.target.value)}
                                required
                                autoComplete="name"
                            />
                        </label>
                        {errors.name ? <p className="auth-page__error">{errors.name}</p> : null}

                        <label className="auth-page__label" htmlFor="google-register-mobile">
                            Mobile
                            <input
                                id="google-register-mobile"
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

                                    <label className="auth-page__label" htmlFor={`google-address-label-${index}`}>
                                        Label
                                        <select
                                            id={`google-address-label-${index}`}
                                            className="auth-page__input"
                                            value={address.label}
                                            onChange={(event) => updateAddress(index, 'label', event.target.value)}
                                        >
                                            {addressLabels.map((label) => (
                                                <option key={label} value={label}>
                                                    {label}
                                                </option>
                                            ))}
                                        </select>
                                    </label>

                                    <label className="auth-page__label" htmlFor={`google-address-full-${index}`}>
                                        Full address
                                        <textarea
                                            id={`google-address-full-${index}`}
                                            className="auth-page__textarea"
                                            rows={3}
                                            value={address.full_address}
                                            onChange={(event) =>
                                                updateAddress(index, 'full_address', event.target.value)
                                            }
                                            required
                                        />
                                    </label>
                                    {errors[`addresses.${index}.full_address`] ? (
                                        <p className="auth-page__error">
                                            {errors[`addresses.${index}.full_address`]}
                                        </p>
                                    ) : null}

                                    <div className="auth-address__grid">
                                        <label className="auth-page__label" htmlFor={`google-address-pincode-${index}`}>
                                            Pincode
                                            <input
                                                id={`google-address-pincode-${index}`}
                                                className="auth-page__input"
                                                type="text"
                                                value={address.pincode}
                                                onChange={(event) =>
                                                    updateAddress(index, 'pincode', event.target.value)
                                                }
                                                required
                                                inputMode="numeric"
                                            />
                                        </label>
                                        <label className="auth-page__label" htmlFor={`google-address-district-${index}`}>
                                            District
                                            <input
                                                id={`google-address-district-${index}`}
                                                className="auth-page__input"
                                                type="text"
                                                value={address.district}
                                                onChange={(event) =>
                                                    updateAddress(index, 'district', event.target.value)
                                                }
                                                required
                                            />
                                        </label>
                                        <label className="auth-page__label" htmlFor={`google-address-state-${index}`}>
                                            State
                                            <input
                                                id={`google-address-state-${index}`}
                                                className="auth-page__input"
                                                type="text"
                                                value={address.state}
                                                onChange={(event) =>
                                                    updateAddress(index, 'state', event.target.value)
                                                }
                                                required
                                            />
                                        </label>
                                    </div>

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
                            {processing ? 'Saving...' : 'Complete registration'}
                        </button>
                    </form>
                </div>
            </section>
        </StorefrontLayout>
    );
}
