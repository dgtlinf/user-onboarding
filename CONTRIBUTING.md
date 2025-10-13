
# 🤝 Contributing Guidelines

First off — thank you for taking the time to contribute!  
We welcome all contributions — bug reports, ideas, tests, and pull requests.

---

## 🧩 How to Contribute

### 1️⃣ Fork and Clone
Start by forking this repository, then clone it locally:

```bash
git clone https://github.com/<your-username>/<repo-name>.git
cd <repo-name>
composer install
```

---

### 2️⃣ Run the Tests
Before submitting any pull request, make sure all tests pass:

```bash
vendor/bin/pest
```

> Tests use [Orchestra Testbench](https://github.com/orchestral/testbench) and an in-memory SQLite database.

---

### 3️⃣ Coding Standards
Please follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style.

To check and fix style automatically, run:

```bash
composer fix
```

_(You can add `"scripts": { "fix": "php-cs-fixer fix --using-cache=no" }` in your composer.json if not present.)_

---

### 4️⃣ Submitting a Pull Request
When opening a PR:

- Describe **what** the change does and **why** it’s needed.
- Include relevant **tests** if possible.
- Make sure `vendor/bin/pest` passes locally.
- Update documentation (README or docs) if required.

Use our [Pull Request Template](.github/PULL_REQUEST_TEMPLATE.md) — GitHub will auto-load it.

---

### 5️⃣ Reporting Bugs
If you found a bug, please open a new issue using the [Bug Report Template](.github/ISSUE_TEMPLATE/bug_report.yml).  
Include as much detail as possible — Laravel version, PHP version, package version, and reproduction steps.

---

### 6️⃣ Suggesting Features
For new ideas or improvements, use the [Feature Request Template](.github/ISSUE_TEMPLATE/feature_request.yml).  
We discuss and evaluate based on community need and technical feasibility.

---

## 🛠️ Local Development Tips

- Tests use SQLite in-memory (`:memory:`) — no external DB setup needed.
- You can run specific test files:
  ```bash
  vendor/bin/pest tests/Feature/PasswordlessManagerTest.php
  ```
- Use `dump()` or `ray()` if you prefer interactive debugging.

---

## 🧠 Additional Notes

- Be respectful and follow our [Code of Conduct](CODE_OF_CONDUCT.md).
- Security issues? Please email **office@digitalinfinity.rs** instead of opening an issue.
- We aim to review pull requests within a few business days.

---

**Maintained by:** Digital Infinity DOO Novi Sad  
**Website:** [www.digitalinfinity.rs](https://www.digitalinfinity.rs)
