import { useForm, usePage } from '@inertiajs/react';

export default function ProfileSection({ profile }) {
    const { flash } = usePage().props;
    const { data, setData, put, processing, errors, recentlySuccessful } = useForm({
        name: profile?.name ?? '',
        email: profile?.email ?? '',
        mobile: profile?.phone ?? '',
    });

    const onSubmit = (event) => {
        event.preventDefault();
        put('/account/profile');
    };

    return (
        <section className="account-panel" aria-label="Profile details">
            {flash?.success || recentlySuccessful ? (
                <p className="account-flash" role="status">
                    {flash?.success || 'Profile updated.'}
                </p>
            ) : null}

            <form className="account-form" onSubmit={onSubmit}>
                <label className="account-form__label" htmlFor="profile-name">
                    Full name
                    <input
                        id="profile-name"
                        className="account-form__input"
                        type="text"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                        required
                        autoComplete="name"
                    />
                </label>
                {errors.name ? <p className="account-form__error">{errors.name}</p> : null}

                <label className="account-form__label" htmlFor="profile-mobile">
                    Mobile
                    <input
                        id="profile-mobile"
                        className="account-form__input"
                        type="tel"
                        value={data.mobile}
                        onChange={(event) => setData('mobile', event.target.value)}
                        required
                        autoComplete="tel"
                        inputMode="numeric"
                    />
                </label>
                {errors.mobile ? <p className="account-form__error">{errors.mobile}</p> : null}

                <label className="account-form__label" htmlFor="profile-email">
                    Email <span className="account-form__optional">(optional)</span>
                    <input
                        id="profile-email"
                        className="account-form__input"
                        type="email"
                        value={data.email}
                        onChange={(event) => setData('email', event.target.value)}
                        autoComplete="email"
                    />
                </label>
                {errors.email ? <p className="account-form__error">{errors.email}</p> : null}

                <p className="account-form__meta">Member since {profile?.memberSince || '-'}</p>

                <button type="submit" className="btn btn--primary btn--block" disabled={processing}>
                    {processing ? 'Saving...' : 'Save changes'}
                </button>
            </form>
        </section>
    );
}
