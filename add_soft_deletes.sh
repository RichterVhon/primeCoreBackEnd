#!/bin/bash

MODEL_DIR="app/Models"

find $MODEL_DIR -type f -name "*.php" | while read file; do
    if ! grep -q "SoftDeletes" "$file"; then
        echo "Fixing $file..."

        # Insert the import after namespace
        sed -i '/^namespace/a use Illuminate\\Database\\Eloquent\\SoftDeletes;' "$file"

        # Insert trait inside class block
        # Match the opening brace and insert after it
        sed -i '/class .*extends.*Model.*/{
            N
            s/\n{/ {\n    use SoftDeletes;/
        }' "$file"
    fi
done

echo "SoftDeletes correctly added to all models!"

