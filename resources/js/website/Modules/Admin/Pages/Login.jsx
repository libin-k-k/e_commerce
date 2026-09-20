import { Head, useForm, usePage } from '@inertiajs/react';

export default function Login({ seo }) {
    const { appName } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const onSubmit = (event) => {
        event.preventDefault();
        post('/admin/login');
    };

    return (
        <div className="admin-login">
            <Head title={seo?.title} />
            <div className="admin-login__card">
                <p className="admin-login__brand">{appName}</p>
                <h1 className="admin-login__title">Admin sign in</h1>
                <p className="admin-login__text">Use your admin credentials to open the dashboard.</p>

                <form className="admin-login__form" onSubmit={onSubmit}>
                    <label className="admin-login__label" htmlFor="admin-email">
                        Email
                    </label>
                    <input
                        id="admin-email"
                        className="admin-login__input"
                        type="email"
                        name="email"
                        value={data.email}
                        onChange={(event) => setData('email', event.target.value)}
                        autoComplete="username"
                        required
                    />
                    {errors.email ? <p className="admin-login__error">{errors.email}</p> : null}

                    <label className="admin-login__label" htmlFor="admin-password">
                        Password
                    </label>
                    <input
                        id="admin-password"
                        className="admin-login__input"
                        type="password"
                        name="password"
                        value={data.password}
                        onChange={(event) => setData('password', event.target.value)}
                        autoComplete="current-password"
                        required
                    />
                    {errors.password ? <p className="admin-login__error">{errors.password}</p> : null}

                    <label className="admin-login__remember">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={(event) => setData('remember', event.target.checked)}
                        />
                        Remember me
                    </label>

                    <button type="submit" className="btn btn--primary btn--block" disabled={processing}>
                        {processing ? 'Signing in...' : 'Sign in'}
                    </button>
                </form>
            </div>
        </div>
    );
}
