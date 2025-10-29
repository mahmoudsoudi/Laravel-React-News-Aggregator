import React, { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import './Dashboard.css';

const Dashboard: React.FC = () => {
  const { user, logout, updateProfile, isLoading } = useAuth();
  const [isEditing, setIsEditing] = useState(false);
  const [formData, setFormData] = useState({
    name: user?.name || '',
    email: user?.email || '',
    password: '',
    password_confirmation: '',
  });
  const [errors, setErrors] = useState<{ [key: string]: string }>({});
  const [isUpdating, setIsUpdating] = useState(false);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value,
    }));
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: '',
      }));
    }
  };

  const handleUpdateProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsUpdating(true);
    setErrors({});

    // Client-side validation
    if (formData.password && formData.password !== formData.password_confirmation) {
      setErrors({ password_confirmation: 'Passwords do not match' });
      setIsUpdating(false);
      return;
    }

    try {
      const updateData: any = {
        name: formData.name,
        email: formData.email,
      };

      if (formData.password) {
        updateData.password = formData.password;
        updateData.password_confirmation = formData.password_confirmation;
      }

      await updateProfile(updateData);
      setIsEditing(false);
      setFormData({
        name: user?.name || '',
        email: user?.email || '',
        password: '',
        password_confirmation: '',
      });
    } catch (err: any) {
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      }
    } finally {
      setIsUpdating(false);
    }
  };

  const handleDeleteAccount = async () => {
    // TODO: Implement delete account functionality
    console.log('Delete account functionality not implemented yet');
  };

  const cancelEdit = () => {
    setIsEditing(false);
    setFormData({
      name: user?.name || '',
      email: user?.email || '',
      password: '',
      password_confirmation: '',
    });
    setErrors({});
  };

  if (isLoading) {
    return (
      <div className="dashboard-container">
        <div className="loading">Loading...</div>
      </div>
    );
  }

  return (
    <div className="dashboard-container">
      <div className="dashboard-header">
        <h1>Dashboard</h1>
        <button onClick={logout} className="logout-button">
          Logout
        </button>
      </div>

      <div className="profile-card">
        <div className="profile-header">
          <h2>Profile Information</h2>
          {!isEditing && (
            <button onClick={() => setIsEditing(true)} className="edit-button">
              Edit Profile
            </button>
          )}
        </div>

        {isEditing ? (
          <form onSubmit={handleUpdateProfile} className="profile-form">
            <div className="form-group">
              <label htmlFor="name">Full Name</label>
              <input
                type="text"
                id="name"
                name="name"
                value={formData.name}
                onChange={handleChange}
                className={errors.name ? 'error' : ''}
                required
              />
              {errors.name && <span className="field-error">{errors.name}</span>}
            </div>

            <div className="form-group">
              <label htmlFor="email">Email</label>
              <input
                type="email"
                id="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                className={errors.email ? 'error' : ''}
                required
              />
              {errors.email && <span className="field-error">{errors.email}</span>}
            </div>

            <div className="form-group">
              <label htmlFor="password">New Password (leave blank to keep current)</label>
              <input
                type="password"
                id="password"
                name="password"
                value={formData.password}
                onChange={handleChange}
                className={errors.password ? 'error' : ''}
                minLength={8}
              />
              {errors.password && <span className="field-error">{errors.password}</span>}
            </div>

            {formData.password && (
              <div className="form-group">
                <label htmlFor="password_confirmation">Confirm New Password</label>
                <input
                  type="password"
                  id="password_confirmation"
                  name="password_confirmation"
                  value={formData.password_confirmation}
                  onChange={handleChange}
                  className={errors.password_confirmation ? 'error' : ''}
                  minLength={8}
                />
                {errors.password_confirmation && (
                  <span className="field-error">{errors.password_confirmation}</span>
                )}
              </div>
            )}

            <div className="form-actions">
              <button
                type="button"
                onClick={cancelEdit}
                className="cancel-button"
                disabled={isUpdating}
              >
                Cancel
              </button>
              <button
                type="submit"
                className="save-button"
                disabled={isUpdating}
              >
                {isUpdating ? 'Saving...' : 'Save Changes'}
              </button>
            </div>
          </form>
        ) : (
          <div className="profile-info">
            <div className="info-item">
              <label>Name:</label>
              <span>{user?.name}</span>
            </div>
            <div className="info-item">
              <label>Email:</label>
              <span>{user?.email}</span>
            </div>
            <div className="info-item">
              <label>Member since:</label>
              <span>{user?.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}</span>
            </div>
          </div>
        )}

        <div className="danger-zone">
          <h3>Danger Zone</h3>
          <p>Once you delete your account, there is no going back. Please be certain.</p>
          <button
            onClick={() => setShowDeleteConfirm(true)}
            className="delete-button"
          >
            Delete Account
          </button>
        </div>
      </div>

      {showDeleteConfirm && (
        <div className="modal-overlay">
          <div className="modal">
            <h3>Delete Account</h3>
            <p>Are you sure you want to delete your account? This action cannot be undone.</p>
            <div className="modal-actions">
              <button
                onClick={() => setShowDeleteConfirm(false)}
                className="cancel-button"
              >
                Cancel
              </button>
              <button
                onClick={handleDeleteAccount}
                className="delete-button"
              >
                Yes, Delete Account
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Dashboard;
