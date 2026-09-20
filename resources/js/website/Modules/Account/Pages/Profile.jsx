import AccountShell from '../Components/AccountShell';
import AccountSubpageHeader from '../Components/AccountSubpageHeader';
import ProfileSection from '../Sections/ProfileSection';

export default function Profile({ seo, profile, menu = [], active = 'profile' }) {
    return (
        <AccountShell seo={seo} menu={menu} profile={profile} active={active}>
            <AccountSubpageHeader title="Profile" />
            <ProfileSection profile={profile} />
        </AccountShell>
    );
}
