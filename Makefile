.PHONY: deploy

deploy:
    	set -e; \
    	git pull origin main; \
    	npm ci; \
    	npm run build; \
