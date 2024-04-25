# BOOM Online Classic

| Preview Setup                      | Preview Play                           |
| ---------------------------------- | -------------------------------------- |
| ![boom-setup](/images/preview.png) | ![boom-play](/images/preview-play.png) |

# Get Started

- Simple run docker

```bash
# docker compose
docker-compose up -d

# or docker
docker build -t boom .
docker run -p 8080:80 -v $(pwd):/var/www/html boom
```

# Author

- ThinhHV <https://github.com/nakamuraos>
