# Simple HiAnime Player

A lightweight PHP-based anime player utilizing Vidstack, designed to work seamlessly with a custom `hianime-api` endpoint.

## 🚀 Features
- Streams anime episodes using the HiAnime API.
- Supports JSON-formatted responses for seamless playback.
- Plays episodes via m3u8 links.
- Includes subtitles and thumbnail previews.
- Chapter support for enhanced viewing experience.

---

## 📌 How to Use
### 1️⃣ Deploy the API & Proxy
Ensure you have an API compatible with `/api/v2/hianime/episode/sources`. You can use a modified version of [aniwatch-api](https://github.com/ghoshRitesh12/aniwatch-api) & [m3u8-proxy](https://github.com/itzzzme/m3u8proxy) that aligns with this endpoint.

### 2️⃣ Configure the API URL
Modify `player.php` to set your API URL:
```php
$myapi_url = "https://your-api-here.vercel.app";
$proxy_url = "https://your-proxy-here.com/m3u8-proxy";
```

### 3️⃣ Host the Player
Upload the files to a PHP-enabled server such as XAMPP, a shared hosting service, or a VPS.

### 4️⃣ Access the Player
Use the following URL structure to access the player:
```
http://your-site.com/player.php?id=<animeEpisodeId>&server=hd-1&category=sub
```

---

## 📄 Query Parameters
| Parameter       | Type   | Description                                  | Required? | Default |
|----------------|--------|----------------------------------------------|-----------|---------|
| `animeEpisodeId` | string | The unique anime episode ID.                | ✅ Yes    | --      |
| `server`       | string | The streaming server name.                  | ❌ No     | "hd-2"  |
| `category`     | string | Episode category (`sub`, `dub`, `raw`).      | ❌ No     | "sub"   |

### 🔹 Example Usage
```plaintext
http://localhost/player.php?id=jujutsu-kaisen-2nd-season-18413&ep=102662
http://localhost/player.php?id=jujutsu-kaisen-2nd-season-18413&ep=102662&server=hd-2&type=sub
https://yourdomain.com/player.php?id=jujutsu-kaisen-2nd-season-18413&ep=102662&server=hd-2&type=dub
```

---

## 🛠 Requirements
- PHP 7+ with `file_get_contents` enabled.
- An API with the following endpoint:
  ```plaintext
  /api/v2/hianime/episode/sources?animeEpisodeId={id}&server={server}&type={sub|dub|raw}
  ```

---

## 🔍 How It Works (For Beginners)
This PHP script functions as follows:
1. Extracts the episode ID from the URL (e.g., `jujutsu-kaisen-2nd-season-18413?ep=102662`).
2. Fetches video sources from the API (`https://your-api.vercel.app/api/v2/hianime/episode/sources`).
3. Renders the video player using Vidstack, including subtitles and skip buttons.

**New to PHP?** Just upload `player.php` to a PHP-supported server, set your API URL, and open the page in a browser with an episode ID. 🎉

---

## ⚡ CLI Setup (For Advanced Users)
Clone and run the player locally:
```bash
# Clone the repository
git clone https://github.com/your-username/simple-hianime-player.git

# Navigate into the folder
cd simple-hianime-player

# Edit the API URL in player.php
nano player.php  # Replace $myapi_url with your API URL

# Start a local PHP server
php -S localhost:8000

# Open in browser
http://localhost:8000/player.php?id=jujutsu-kaisen-2nd-season-18413&ep=102662
```

---

## 🤝 Contributors
This project is made possible by the contributions of:
- **Hamza Wolf** - Creator & Maintainer.
- **Ritesh Ghosh** - API Developer [(GitHub)](https://github.com/ghoshRitesh12/aniwatch-api).
- **Sayan (itzzzme)** - Proxy Developer [(GitHub)](https://github.com/itzzzme/m3u8proxy).
- **HiAnime** - For inspiration.
- **Vidstack Player** - [vidstack.io](https://vidstack.io/).

### 💡 Contribute
Contributions are welcome! Fork the repository, make your changes, and submit a pull request. 🚀

---

## 📜 License
This project is open-source under the MIT License.
