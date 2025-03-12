# Simple Custom MemberPress Paywall Plugin
---

## 📌 Features
✅ **Editable Free Views** → Define how many free views a logged-in user gets.  
✅ **Supports Multiple Rule IDs** → Users under specific Rule IDs bypass the paywall.  
✅ **Fully Integrated with WordPress Admin** → Set options in `Settings → MemberPress Paywall`.  
✅ **Guest Users Blocked Immediately** → Only logged-in users get free views.  
✅ **Secure View Tracking via Cookies** → Prevents users from bypassing the limit.  
✅ **Does Not Modify MemberPress Core Files** → Uses hooks & filters for smooth updates.

---

## 🚀 Installation

1. **Download & Extract the Plugin:**
   ```sh
   git clone https://github.com/eamirakulov/custom-memberpress-paywall.git
   ```
2. **Move the plugin folder to WordPress plugins directory:**
   ```sh
   mv custom-memberpress-paywall /wp-content/plugins/
   ```
3. **Activate the plugin in WordPress Admin:**
   - Navigate to `Plugins → Installed Plugins`.
   - Find **Custom MemberPress Paywall** and click **Activate**.

---

## 🔧 Configuration

1. Go to **`Settings → MemberPress Paywall`** in the WordPress admin.
2. Set the **Number of Free Views** before the paywall activates.
3. Enter **Allowed Rule IDs** (comma-separated) to bypass the paywall.
   - Example: `71, 102, 200`
4. Click **Save Changes**.

---

## 📌 How It Works
- **Guests are blocked completely** (must log in to access content).
- **Logged-in users** get `X` free views (as set in settings).
- **Users under specified Rule IDs** can access content **without any restrictions**.
- **Paywall activates once free views are exhausted**.

---

## 🛠️ Development

### Modify & Extend the Plugin:
- Clone the repository:
  ```sh
  git clone https://github.com/eamirakulov/custom-memberpress-paywall.git
  ```
- Edit the plugin files (`custom-memberpress-paywall.php`) as needed.
- Push changes:
  ```sh
  git add .
  git commit -m "Added new feature"
  git push origin main
  ```

---

## 📜 License
This plugin is open-source and licensed under the **GPL-2.0 License**.

---

## 🛠 Support & Issues
If you find a bug or want to request a feature, feel free to:
- Open an issue: [GitHub Issues](https://github.com/eamirakulov/custom-memberpress-paywall/issues)
- Submit a pull request: [GitHub PRs](https://github.com/eamirakulov/custom-memberpress-paywall/pulls)

---

🚀 **Developed & Maintained by Emil Amirakulov(https://www.upwork.com/freelancers/~01934ce3183276c713)**  
🌟 If you find this plugin useful, give it a ⭐ on GitHub!  
