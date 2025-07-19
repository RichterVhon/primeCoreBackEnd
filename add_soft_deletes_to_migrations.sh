#!/bin/bash

MIGRATION_DIR="database/migrations"

find $MIGRATION_DIR -type f -name "*.php" | while read file; do
    if ! grep -q "softDeletes" "$file"; then
        echo "Updating $file..."

        # Add the softDeletes line before the final closing });
        sed -i '/Schema::create.*function.*Blueprint.*table/,/});/ {
            /});/ i \        $table->softDeletes();
        }' "$file"
    fi
done

echo "✅ softDeletes line inserted where missing!"
