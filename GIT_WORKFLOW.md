# Git Workflow Quick Reference

## 🚀 **Quick Start Commands**

### **Initial Setup:**
```bash
# Clone the repository
git clone https://github.com/yourusername/aggregator.git
cd aggregator

# Set up the project
./setup.sh
```

### **Daily Development:**
```bash
# Check status
git status

# Create feature branch
git checkout -b feature/new-feature

# Make changes, then stage
git add .

# Commit with conventional format
git commit -m "feat: add new authentication feature"

# Push to remote
git push origin feature/new-feature
```

## 📋 **Branch Naming Convention**

### **Branch Types:**
- `feature/description` - New features
- `fix/description` - Bug fixes
- `hotfix/description` - Critical fixes
- `docs/description` - Documentation updates
- `refactor/description` - Code refactoring

### **Examples:**
```bash
feature/user-profile-management
fix/login-validation-error
hotfix/security-vulnerability
docs/api-documentation-update
refactor/database-query-optimization
```

## 📝 **Commit Message Format**

### **Conventional Commits:**
```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### **Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### **Examples:**
```bash
git commit -m "feat(auth): add two-factor authentication"
git commit -m "fix(api): resolve CORS issues"
git commit -m "docs: update setup instructions"
git commit -m "style(frontend): improve button styling"
git commit -m "refactor(backend): optimize database queries"
git commit -m "test: add unit tests for user service"
git commit -m "chore: update dependencies"
```

## 🔄 **Workflow Steps**

### **1. Feature Development:**
```bash
# Start from main
git checkout main
git pull origin main

# Create feature branch
git checkout -b feature/user-dashboard

# Make changes
# ... edit files ...

# Stage changes
git add .

# Commit
git commit -m "feat(dashboard): add user profile management"

# Push
git push origin feature/user-dashboard
```

### **2. Bug Fix:**
```bash
# Start from main
git checkout main
git pull origin main

# Create fix branch
git checkout -b fix/login-error

# Make changes
# ... fix the bug ...

# Stage and commit
git add .
git commit -m "fix(auth): resolve login validation error"

# Push
git push origin fix/login-error
```

### **3. Hotfix (Critical):**
```bash
# Start from main
git checkout main
git pull origin main

# Create hotfix branch
git checkout -b hotfix/security-patch

# Make critical fix
# ... fix security issue ...

# Stage and commit
git add .
git commit -m "hotfix(security): patch authentication vulnerability"

# Push and merge immediately
git push origin hotfix/security-patch
```

## 🏷️ **Tagging Releases**

### **Create Release:**
```bash
# Tag the release
git tag -a v1.0.0 -m "Release version 1.0.0"

# Push tags
git push origin v1.0.0
```

### **Version Numbering:**
- `v1.0.0` - Major release (breaking changes)
- `v1.1.0` - Minor release (new features)
- `v1.1.1` - Patch release (bug fixes)

## 🔍 **Useful Commands**

### **View History:**
```bash
# View commit history
git log --oneline

# View changes in a file
git log -p filename

# View changes between branches
git diff main..feature-branch
```

### **Undo Changes:**
```bash
# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1

# Undo changes to a file
git checkout -- filename
```

### **Branch Management:**
```bash
# List branches
git branch -a

# Delete local branch
git branch -d feature-branch

# Delete remote branch
git push origin --delete feature-branch

# Switch branches
git checkout branch-name
```

## 🚨 **Emergency Procedures**

### **Revert a Commit:**
```bash
# Revert specific commit
git revert <commit-hash>

# Revert merge commit
git revert -m 1 <merge-commit-hash>
```

### **Reset to Previous State:**
```bash
# Reset to specific commit
git reset --hard <commit-hash>

# Reset to remote main
git reset --hard origin/main
```

## 📊 **Best Practices**

### **Do:**
- ✅ Use descriptive commit messages
- ✅ Make small, focused commits
- ✅ Test before committing
- ✅ Use feature branches
- ✅ Keep main branch stable
- ✅ Review code before merging

### **Don't:**
- ❌ Commit directly to main
- ❌ Commit broken code
- ❌ Use vague commit messages
- ❌ Mix unrelated changes in one commit
- ❌ Force push to shared branches

## 🔧 **Configuration**

### **Set up Git user:**
```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### **Set up aliases:**
```bash
git config --global alias.st status
git config --global alias.co checkout
git config --global alias.br branch
git config --global alias.ci commit
git config --global alias.unstage 'reset HEAD --'
```

## 🎯 **Integration with Docker**

### **Development Workflow:**
```bash
# Make changes
git add .
git commit -m "feat: add new feature"

# Test with Docker
docker compose up -d
docker compose logs -f

# If everything works, push
git push origin feature-branch
```

This workflow integrates perfectly with your Docker setup and ensures consistent development practices across your team!
