import { useState } from 'react';
import { router, useForm, usePage } from '@inertiajs/react';

const emptyAddress = (isDefault = false) => ({
    label: 'Home',
    full_address: '',
    pincode: '',
    district: '',
    state: '',
    is_default: isDefault,
});

function AddressFormFields({
    idPrefix,
    data,
    setData,
    errors,
    addressLabels,
    processing = false,
    showRemove = false,
    onRemove,
}) {
    return (
        <div className="account-address-form">
            <label className="account-form__label" htmlFor={`${idPrefix}-label`}>
                Label
                <select
                    id={`${idPrefix}-label`}
                    className="account-form__input"
                    value={data.label}
                    onChange={(event) => setData('label', event.target.value)}
                >
                    {addressLabels.map((label) => (
                        <option key={label} value={label}>
                            {label}
                        </option>
                    ))}
                    {! addressLabels.includes(data.label) ? (
                        <option value={data.label}>{data.label}</option>
                    ) : null}
                </select>
            </label>
            {errors.label ? <p className="account-form__error">{errors.label}</p> : null}

            <label className="account-form__label" htmlFor={`${idPrefix}-full`}>
                Full address
                <textarea
                    id={`${idPrefix}-full`}
                    className="account-form__textarea"
                    rows={3}
                    value={data.full_address}
                    onChange={(event) => setData('full_address', event.target.value)}
                    required
                />
            </label>
            {errors.full_address ? <p className="account-form__error">{errors.full_address}</p> : null}

            <div className="account-address-form__grid">
                <label className="account-form__label" htmlFor={`${idPrefix}-pincode`}>
                    Pincode
                    <input
                        id={`${idPrefix}-pincode`}
                        className="account-form__input"
                        type="text"
                        value={data.pincode}
                        onChange={(event) => setData('pincode', event.target.value)}
                        required
                        inputMode="numeric"
                    />
                </label>
                <label className="account-form__label" htmlFor={`${idPrefix}-district`}>
                    District
                    <input
                        id={`${idPrefix}-district`}
                        className="account-form__input"
                        type="text"
                        value={data.district}
                        onChange={(event) => setData('district', event.target.value)}
                        required
                    />
                </label>
                <label className="account-form__label" htmlFor={`${idPrefix}-state`}>
                    State
                    <input
                        id={`${idPrefix}-state`}
                        className="account-form__input"
                        type="text"
                        value={data.state}
                        onChange={(event) => setData('state', event.target.value)}
                        required
                    />
                </label>
            </div>
            {(errors.pincode || errors.district || errors.state) ? (
                <p className="account-form__error">
                    {errors.pincode || errors.district || errors.state}
                </p>
            ) : null}

            <label className="account-form__check">
                <input
                    type="checkbox"
                    checked={Boolean(data.is_default)}
                    onChange={(event) => setData('is_default', event.target.checked)}
                />
                Default address
            </label>

            <div className="account-address-form__actions">
                <button type="submit" className="btn btn--primary" disabled={processing}>
                    {processing ? 'Saving...' : 'Save address'}
                </button>
                {showRemove ? (
                    <button type="button" className="btn btn--ghost" onClick={onRemove} disabled={processing}>
                        Remove
                    </button>
                ) : null}
            </div>
        </div>
    );
}

function EditAddressCard({ address, addressLabels }) {
    const form = useForm({
        label: address.label || 'Home',
        full_address: address.full_address || address.line1 || '',
        pincode: address.pincode || '',
        district: address.district || address.city || '',
        state: address.state || '',
        is_default: Boolean(address.isDefault),
    });

    const onSubmit = (event) => {
        event.preventDefault();
        form.put(`/account/addresses/${address.id}`);
    };

    const onRemove = () => {
        if (! window.confirm('Remove this address?')) {
            return;
        }

        router.delete(`/account/addresses/${address.id}`);
    };

    return (
        <li className="account-address account-address--editable">
            <form onSubmit={onSubmit}>
                <AddressFormFields
                    idPrefix={`edit-${address.id}`}
                    data={form.data}
                    setData={form.setData}
                    errors={form.errors}
                    addressLabels={addressLabels}
                    processing={form.processing}
                    showRemove
                    onRemove={onRemove}
                />
            </form>
        </li>
    );
}

function CreateAddressForm({ addressLabels, hasAddresses }) {
    const [open, setOpen] = useState(! hasAddresses);
    const form = useForm(emptyAddress(! hasAddresses));

    const onSubmit = (event) => {
        event.preventDefault();
        form.post('/account/addresses', {
            onSuccess: () => {
                form.reset();
                form.setData(emptyAddress(false));
                setOpen(false);
            },
        });
    };

    if (! open) {
        return (
            <button type="button" className="btn btn--ghost btn--block" onClick={() => setOpen(true)}>
                Add address
            </button>
        );
    }

    return (
        <form className="account-address account-address--editable" onSubmit={onSubmit}>
            <h2 className="account-address__label">New address</h2>
            <AddressFormFields
                idPrefix="create"
                data={form.data}
                setData={form.setData}
                errors={form.errors}
                addressLabels={addressLabels}
                processing={form.processing}
            />
            <button
                type="button"
                className="btn btn--ghost btn--sm"
                onClick={() => {
                    form.reset();
                    setOpen(false);
                }}
            >
                Cancel
            </button>
        </form>
    );
}

export default function AddressesSection({
    addresses = [],
    addressLabels = ['Home', 'Work', 'Other'],
}) {
    const { flash } = usePage().props;

    return (
        <section className="account-panel" aria-label="Saved addresses">
            {flash?.success ? (
                <p className="account-flash" role="status">
                    {flash.success}
                </p>
            ) : null}

            {addresses.length === 0 ? (
                <p className="account-empty">No saved addresses yet.</p>
            ) : (
                <ul className="account-address-list">
                    {addresses.map((address) => (
                        <EditAddressCard
                            key={address.id}
                            address={address}
                            addressLabels={addressLabels}
                        />
                    ))}
                </ul>
            )}

            <CreateAddressForm addressLabels={addressLabels} hasAddresses={addresses.length > 0} />
        </section>
    );
}
