# AI-Powered Development with Backpack

Backpack for Laravel now ships AI-optimized documentation that integrates directly into your AI coding agent (GitHub Copilot, Cursor, Claude Code, and others). When installed, your AI agent gains expert Backpack knowledge, a dedicated documentation search tool, and detailed skill guides — all versioned with your installed ```backpack/crud``` package.

---

## What You Get

| Feature | Description |
|---------|-------------|
| **AI Guidelines** | Backpack conventions and best practices injected into your agent's instruction file (```AGENTS.md```, ```.cursor/rules```, ```CLAUDE.md```, etc.). Your AI automatically follows Backpack patterns for CRUD generation, fields, columns, filters, operations, buttons, and more. |
| **Agent Skills** | Detailed skill guides for specific topics (fields, columns, filters, uploaders, testing, editable columns, data form modals, JavaScript API, etc.). The agent activates the relevant skill automatically based on what you're working on. |
| **MCP Search Tool** | A dedicated ```search-backpack-docs``` tool that searches Backpack's official documentation directly. Unlike generic web searches, this returns version-specific, structured results from the Backpack docs. |

---

## Prerequisites

- **```backpack/crud```** — installed and configured in your Laravel application.
- **```laravel/boost```** — the Laravel Boost package that powers AI agent integration.

If you don't have ```laravel/boost``` yet, install it:

```bash
composer require laravel/boost
```

---

## Installation

If you're running ```backpack:install``` for a fresh setup, you'll be prompted to add Backpack AI guidelines automatically — just say **yes** and you're done.

If you already have Backpack installed, simply re-run the Boost installer to pick up the Backpack guidelines and skills:

```bash
php artisan boost:install
```

During the installation, you'll see a prompt to **"Select third-party AI guidelines/skills"**. Make sure **```backpack/crud```** is checked, then press Enter. (If you've run the installer before, ```backpack/crud``` may already be pre-selected.)

After installation, your AI agent's config file will include Backpack guidelines, and the ```search-backpack-docs``` MCP tool will be available in the ```laravel-boost``` MCP server.

---

## Using the Backpack Documentation Search

The ```search-backpack-docs``` MCP tool is the **primary way** your AI agent accesses Backpack documentation. To get the best results:

### Mention "Backpack" in your prompt

The MCP server needs context to know which tool to use. Include the word **"backpack"** in your question so the agent routes to the correct search tool:

The tool works best with Backpack-specific terms. Mention field types, operation names, or CRUD concepts:

> ✅ *"How do I configure a backpack ```select2_from_ajax``` field with a custom route?"*
>
> ✅ *"Show me how to add a ```CloneOperation``` to my backpack CrudController."*
>
> ✅ *"What's the backpack syntax for ```CRUD::addFilter``` with a date_range?"*

### Multiple queries for broader results

The tool accepts multiple search queries and returns results for all of them. Your AI agent will use this automatically when unsure of exact terminology:

```
search-backpack-docs(["relationship field", "select2 belongsTo", "belongsToMany"])
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| AI doesn't seem to know Backpack conventions | Re-run ```php artisan boost:install``` and ensure ```backpack/crud``` is selected for third-party packages. |
| Agent uses generic searches instead of Backpack docs | Include the word **"backpack"** in your prompts so the agent knows to use ```search-backpack-docs```. |
| Missing assets or broken uploads | Run ```php artisan storage:link -q``` — the storage symlink is often missing on new setups. |
| Boost MCP tools not available | Check that ```laravel/boost``` is installed and ```boost:install``` has been run. The ```boost.json``` file in your project root should list ```backpack/crud``` under ```packages```. |
