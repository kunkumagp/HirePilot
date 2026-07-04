import { useState, useEffect } from 'react';
import { authApi } from '../../api/auth';
import { Card, CardHeader, CardTitle, CardContent } from '../../components/ui/card';
import { Input } from '../../components/ui/input';
import { Button } from '../../components/ui/button';
import { Alert } from '../../components/ui/alert';
import { Spinner } from '../../components/ui/spinner';
import type { User, Profile } from '../../types/auth';
import { AxiosError } from 'axios';

interface FormState {
  user: User;
  profile: Profile;
}

export function ProfilePage() {
  const [data, setData] = useState<FormState | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [success, setSuccess] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await authApi.me();
        const result = response.data.data;
        setData({
          user: result.user,
          profile: result.profile ?? {
            avatar: null, phone: null, headline: null, bio: null,
            location: null, linkedin_url: null, github_url: null,
            website_url: null, preferences: null,
          },
        });
      } catch {
        setError('Failed to load profile');
      } finally {
        setIsLoading(false);
      }
    };
    fetchData();
  }, []);

  const updateUserField = (field: keyof User, value: string) => {
    if (!data) return;
    setData({ ...data, user: { ...data.user, [field]: value } });
  };

  const updateProfileField = (field: keyof Profile, value: string) => {
    if (!data) return;
    setData({ ...data, profile: { ...data.profile, [field]: value } });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!data) return;

    setIsSaving(true);
    setError(null);
    setSuccess(null);

    try {
      await authApi.updateProfile({
        name: data.user.name,
        timezone: data.user.timezone,
        phone: data.profile.phone || undefined,
        headline: data.profile.headline || undefined,
        bio: data.profile.bio || undefined,
        location: data.profile.location || undefined,
        linkedin_url: data.profile.linkedin_url || undefined,
        github_url: data.profile.github_url || undefined,
        website_url: data.profile.website_url || undefined,
      });
      setSuccess('Profile updated successfully.');
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Update failed.');
    } finally {
      setIsSaving(false);
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!data) {
    return <Alert variant="error" message="Failed to load profile." />;
  }

  return (
    <div className="mx-auto max-w-2xl">
      <h1 className="mb-6 text-2xl font-semibold">Profile Settings</h1>
      <Card>
        <CardHeader>
          <CardTitle>Personal Information</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            {success && <Alert variant="success" message={success} />}
            {error && <Alert variant="error" message={error} />}

            <Input
              label="Name"
              value={data.user.name}
              onChange={(e) => updateUserField('name', e.target.value)}
            />
            <Input label="Email" value={data.user.email} disabled />
            <Input
              label="Headline"
              value={data.profile.headline || ''}
              onChange={(e) => updateProfileField('headline', e.target.value)}
              placeholder="e.g. Senior Software Engineer"
            />
            <div className="space-y-1.5">
              <label className="block text-sm font-medium text-slate-300">Bio</label>
              <textarea
                className="flex min-h-[100px] w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-slate-900"
                value={data.profile.bio || ''}
                onChange={(e) => updateProfileField('bio', e.target.value)}
                placeholder="Tell us about yourself..."
              />
            </div>
            <Input
              label="Location"
              value={data.profile.location || ''}
              onChange={(e) => updateProfileField('location', e.target.value)}
              placeholder="City, Country"
            />
            <Input
              label="Phone"
              type="tel"
              value={data.profile.phone || ''}
              onChange={(e) => updateProfileField('phone', e.target.value)}
            />
            <Input
              label="LinkedIn URL"
              value={data.profile.linkedin_url || ''}
              onChange={(e) => updateProfileField('linkedin_url', e.target.value)}
            />
            <Input
              label="GitHub URL"
              value={data.profile.github_url || ''}
              onChange={(e) => updateProfileField('github_url', e.target.value)}
            />
            <Input
              label="Website URL"
              value={data.profile.website_url || ''}
              onChange={(e) => updateProfileField('website_url', e.target.value)}
            />
            <div className="pt-2">
              <Button type="submit" isLoading={isSaving}>
                {isSaving ? 'Saving...' : 'Save changes'}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
