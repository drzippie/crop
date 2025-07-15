.PHONY: help install test test-coverage phpstan phpcs phpcbf clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install dependencies
	composer install

test: ## Run tests
	composer test

test-coverage: ## Run tests with coverage
	composer test-coverage

phpstan: ## Run PHPStan static analysis
	composer phpstan

phpcs: ## Run PHP_CodeSniffer
	composer phpcs

phpcbf: ## Run PHP Code Beautifier and Fixer
	composer phpcbf

clean: ## Clean generated files
	rm -rf vendor/
	rm -rf .phpunit.cache/
	rm -rf coverage/
	rm -rf coverage-html/

dev-setup: install ## Set up development environment
	@echo "Development environment ready!"
	@echo "Run 'make test' to run tests"
	@echo "Run 'make phpstan' to run static analysis"
	@echo "Run 'make phpcs' to check code style"