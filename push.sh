#!/usr/bin/env bash
set -e

BRANCH="${1:-main}"
MESSAGE="${2:-Update $(date '+%Y-%m-%d %H:%M')}"

cd "$(dirname "$0")"

echo "==> Pulling latest from origin/${BRANCH}..."
git pull origin "$BRANCH"

echo "==> Staging all changes..."
git add -A

if git diff --cached --quiet; then
    echo "==> No changes to commit."
else
    echo "==> Committing..."
    git commit -m "$MESSAGE"
fi

echo "==> Pushing to origin/${BRANCH}..."
git push origin "$BRANCH"

echo "==> Done. Deploy on server:"
echo "    https://neoranewbie.in/deploy.php?key=meeting123"
