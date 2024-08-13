#!make

help: ## Show this help
	@printf "\033[33m%s:\033[0m\n" 'Run: make <target> where <target> is one of the following'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

phpstan: ## Run PHPStan
	vendor/bin/phpstan analyse --level max src tests

test: phpstan ## Run PHPUnit
	vendor/bin/phpunit --verbose
