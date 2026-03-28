# Obenlo Official Deployment Guide

This guide explains the secure, step-by-step workflow for moving new features or bug fixes from your local computer (`obenlo.local`) to your live production website (`obenlo.com`).

---

## 🛑 Golden Rule
**Never edit code directly on the live server.** Always make your changes locally first, test them, and then use this guide to deploy them.

---

## Phase 1: Local Development to GitHub (The "Dev" Branch)

Your local computer is currently on the `main` branch. Whenever you want to start working on something new, you should switch to the `dev` branch so you don't accidentally break the main code.

### Step 1: Switch to `dev`
Open **PowerShell** and navigate to your Obenlo folder:
```powershell
cd "c:\Users\obenc\Local Sites\obenlo\app\public\wp-content\"
git checkout dev
```
*(If you are already on `dev`, it will safely tell you).*

### Step 2: Make Your Changes
Edit your theme files, plugin files, CSS, etc. using your code editor. Test everything on your local website (`obenlo.local`) to make sure it looks and works perfectly.

### Step 3: Save and Push to GitHub
Once you are happy with the changes, you need to save them ("commit") and push them up to the cloud (GitHub).
In PowerShell, run these three commands one after the other:
```powershell
git add .
git commit -m "Describe what you changed here (e.g., Updated the Favicon)"
git push origin dev
```
Your new code is now safely backed up on the internet (GitHub) in the `dev` branch!

---

## Phase 2: Approving Changes (Merging to `main`)

We use GitHub to safely fuse your new `dev` code into the official `main` code.

1. Open your web browser and go to your repository: [https://github.com/olouisbiz/obenlo.com](https://github.com/olouisbiz/obenlo.com)
2. Near the top, GitHub will usually display a green button saying **Compare & pull request** because it noticed you pushed to `dev`. Click it.
   *(If you don't see it, go to the **Pull requests** tab at the top and click **New pull request**. Set `base: main` and `compare: dev`).*
3. Give your Pull Request a simple title.
4. Click the green **Create pull request** button.
5. GitHub will check to make sure the code doesn't conflict. Once it's green, click **Merge pull request**.
6. Click **Confirm merge**.

Congratulations! The official `main` branch now contains your latest updates.

---

## Phase 3: Deploying to Live SiteGround (Using PuTTY)

Now that GitHub has the approved code, you just need to tell your live SiteGround server to download it.

### Step 1: Open PuTTY
1. Double-click **PuTTY** on your computer.
2. Select **Obenlo Live Site** from your Saved Sessions and click **Load**.
3. Click **Open**. 
*(The black terminal window will appear. Because we configured your Auto-login username and PPK key, it will instantly log you in without asking for a password!)*

### Step 2: Navigate to your Website Folder
You need to tell the server exactly which folder to update. In the black PuTTY window, type this immediately after the `$` prompt:
```bash
cd www/obenlo.com/public_html/wp-content
```
Press **Enter**.

### Step 3: Pull the Updates
Now, tell the server to pull the latest code from the `main` branch on GitHub:
```bash
git pull origin main
```
Press **Enter**.

You will see a checklist of files being downloaded and updated on your screen. 
**That's it! Your live website (`obenlo.com`) is now instantly updated with your newest features!**

---

## 🆘 Troubleshooting: "Already up to date" or Pulling Fails

If you have "force-pushed" a rollback (like we just did) or if the live server gets confused, a simple `git pull` won't work. You need to "Force Sync" the server.

In the black PuTTY window, run these two commands inside the `wp-content` folder:
```bash
git fetch origin
git reset --hard origin/main
```
This will completely overwrite all files on the live site to match exactly what is on GitHub's `main` branch. Use this if you need to "fix" a broken state or roll back.
