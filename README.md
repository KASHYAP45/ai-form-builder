# AI-Powered Form Builder

An AI-powered dynamic form builder built with Laravel, Livewire, MySQL, Blade, Bootstrap and JavaScript.

The application allows administrators to create forms manually, generate forms using AI prompts, organize fields into sections, configure validations, and publish forms through public URLs.

---

## Live Demo

**Demo URL:** Add your deployed URL here

**Demo credentials:**

Email: Add demo email here  
Password: Add demo password here

> If authentication is not configured, remove this section.

---

## GitHub Repository

https://github.com/KASHYAP45/ai-form-builder

---

# Features

## Part A — Core Form Builder

The form builder supports:

- Create forms manually
- Form title and description
- Add fields using buttons
- Multiple field types
- Edit fields inline
- Delete fields
- Duplicate fields
- Drag-and-drop field ordering
- Sections / Steps
- Assign fields to sections
- Required fields
- Field placeholders
- Help text
- Default values
- Field keys
- Options for dropdown, radio and checkbox fields
- Field validation configuration
- Live field preview
- JSON-based form schema
- Public form URL

### Supported Field Types

The builder supports:

1. Text
2. Textarea
3. Email
4. Phone
5. Number
6. Date
7. Dropdown
8. Radio
9. Checkbox
10. File Upload
11. Section Heading
12. Rating

---

# Validation

Field validation can be configured from the form builder.

Supported validation options include:

- Required
- Minimum value
- Maximum value
- Minimum length
- Maximum length
- Numeric
- Email
- URL
- Regex
- File types
- File size

The schema is used as the source of field configuration so that the form structure can be stored and reconstructed.

---

# Public Forms

Every saved form receives a public URL based on its form identifier/slug.

Example:

```text
/forms/{form_id}