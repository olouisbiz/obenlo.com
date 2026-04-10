# Obenlo Official Deployment
# CHANGELOG
## [1.6.7] - 2026-04-07
- Stable recovery point.
- Removed Planner experimental code.
- Protected uploads folder in .gitignore.

This guide explains the secure, step-by-step workflow for moving new features or bug fixes from your local computer (`obenlo.local`) to your live production website (`obenlo.com`).

---

## 🛑 Golden Rule
**Never edit code directly on the live server.** Always make your changes locally first, test them, and then use this guide to deploy them.

---

## 🔑 SSH Credentials Quick Reference
# Obenlo Official Deployment Guide: The Simple Way

This guide explains how to move your code from your local computer to your live website (`obenlo.com`) using **GitHub Desktop** and **PuTTY**.

---

## 🏗 System Architecture

Your development system follows a professional 3-stage process to ensure your code is safe, versioned, and easy to deploy:

1.  **Local (obenlo.local)**: You create and test features here using **Local WP**.
2.  **GitHub (The Bridge)**: You save your work in **GitHub Desktop** and push it to the cloud. This acts as your backup and the "clearing house" for your official code.
3.  **SiteGround (Live Site)**: You use **PuTTY** to tell the live server to download the latest approved code from GitHub.

---

## 🔑 Your New Secure Credentials

The following credentials are built specifically for your new `github_deploy_key`.

| Detail | Value |
| :--- | :--- |
| **Hostname** | `ssh.obenlo.com` |
| **User** | `u2269-codu8dgnuwae` |
| **Port** | `18765` |
| **Public Key File** | `C:\Users\obenc\.ssh\github_deploy_key.pub` |
| **Private Key File** | `C:\Users\obenc\.ssh\github_deploy_key` |

---

## Step 1: Add the Key to SiteGround & GitHub

Before PuTTY can connect, you must give the "Public" key to both SiteGround and GitHub.

### A. SiteGround Setup
1. Open your **SiteGround Site Tools**.
2. Go to **Devs > SSH Keys Manager**.
3. Click the **Import** tab.
4. **Key Name**: `GitHub_Deploy`
5. **Public Key**: Copy and paste the text below exactly:
```text
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCaLkJe02iluJSelZZZDbxJgwJb0DnjvyPY8AwT+CGbpLq7UNXArmRyGL8zuDwilb4HxgcVn56G2ilN/xC8xaBkds+5QTO9Tf9mgl0u/iCDwzO4tXm4pPc0+fTky7WkezPzPF23lqZ48jSvAhj17sRHFky4CGqpvuu6RCmPeBM4MW2J4jBe6mFUPi6y6LQMPoeZ5Ku7iJmC9TRj6M8G6ccD3/gmE8rBy/vVqXRhXDumW4Y9+vkCNyyVnff5je1NvNsxxNVoVQ6D2D/Zj0kWI+phbekyBdXXKMf3gp5cHU9a7Zj/55oo4LzjoxO0vW99PSynahOl0MqNq6YKyTrbp3ET7cTVK5UHE6qoq3yM643qGmolrCRz3utQrODopAIPASd4QEe0a8AFsmNvsuPG17RrVFXavUuwsKZKy9PEI0I/k95baHO9tgxwn3f4H9ghCWFCbijNsw60pEaukfwkp29z3rsnjcd0TRHg9qKNBeV2Y2ZUbuhRuLFSUfFQ/aqltJOrEcJ7wgaHblSyk06jZ7Rn+sEZhVgEkVl5p0EnoFjrse0T29xE8pJczIcYptJytTkWqbiOKZwzBPFrPa4u1rjhYK6+ah3u2QHlIJ78RugcadUpJEU925q1EXQwALuihRZ0TuA/9xypvyzIwumiArR82MvSvlDAjoQmx3w9MksDyQ== obenc@Obenlo-1
```
6. Click **Import**.

### B. GitHub Setup
1. Go to your repository: [https://github.com/olouisbiz/obenlo.com](https://github.com/olouisbiz/obenlo.com)
2. Go to **Settings** (tab at the top) > **Deploy keys**.
3. Click **Add deploy key**.
4. **Title**: `SiteGround_Live`
5. **Key**: Paste the **same text** from above.
6. **Allow write access**: Keep this UNCHECKED (for security).
7. Click **Add key**.

---

## Step 2: Configure PuTTY

PuTTY needs the key in a special `.ppk` format. We will use **PuTTYgen** (which was installed with PuTTY) to do this.

1. Open **PuTTYgen**.
2. Click **Conversions** (in the top menu) > **Import key**.
3. Navigate to `C:\Users\obenc\.ssh\` and select the file named `github_deploy_key`.
4. Click **Save private key** (say "Yes" to saving without a passphrase).
5. Name it `github_deploy_key.ppk` and save it in that same folder.

### Now, setup the connection in PuTTY:
1. Open **PuTTY**.
2. **Host Name**: `ssh.obenlo.com`
3. **Port**: `18765`
4. In the left menu, go to **Connection > SSH > Auth > Credentials**.
5. Click **Browse** under "Private key file for authentication" and select your new `github_deploy_key.ppk`.
6. Go back to **Session** (top of left menu).
7. In "Saved Sessions", type **SiteGround_Live** and click **Save**.
8. Click **Open**.

---

## Step 3: Daily Deployment (The Manual Pull)

Once the setup above is done, this is your daily routine:

1. **Local**: Use **GitHub Desktop** to commit and push your changes to GitHub `main` branch.
2. **PuTTY**: Open PuTTY, load **SiteGround_Live**, and click **Open**.
3. **Login**: Type your username (`u2269-codu8dgnuwae`) and press Enter.
4. **Update**: Type these two lines in the black window:
```bash
cd www/obenlo.com/public_html/wp-content
git pull origin main
```

Instead of manually logging into PuTTY every time, we have set up **GitHub Actions**. This means that as soon as you merge your code into `main` on GitHub, it will automatically send the updates to SiteGround for you.

### How to set up GitHub Secrets (First Time Only)

1. Open your repository on GitHub: [olouisbiz/obenlo.com](https://github.com/olouisbiz/obenlo.com)
2. Go to **Settings** > **Secrets and variables** > **Actions**.
3. Click **New repository secret**.
4. **Name**: `SSH_PRIVATE_KEY`
5. **Value**: (Paste the private key I provided you in our chat).
6. Click **Add secret**.
7. Repeat for `SSH_HOST` (value: `ssh.obenlo.com`), `SSH_USER` (value: `u2269-codu8dgnuwae`), and `SSH_PORT` (value: `18765`).

### How it works
Every time you complete **Phase 2** (Merging to `main`), GitHub will start a "Workflow".
- You can watch it in the **Actions** tab on GitHub.
- It will safely sync your `wp-content` files to SiteGround using `rsync`.
- It automatically ignores temporary files and images in `uploads/`.

---

## 🆘 Troubleshooting: "Already up to date" or Pulling Fails

If you have "force-pushed" a rollback (like we just did) or if the live server gets confused, a simple `git pull` won't work. You need to "Force Sync" the server.

In the black PuTTY window, run these two commands inside the `wp-content` folder:
```bash
git fetch origin
git reset --hard origin/main
```
This will completely overwrite all files on the live site to match exactly what is on GitHub's `main` branch. Use this if you need to "fix" a broken state or roll back.
