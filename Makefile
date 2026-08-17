.PHONY: deploy

deploy:
    	set -e; \
    	git pull origin master; \
    	npm ci; \
    	npm run build; \
