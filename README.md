# Text Steganography In Farsi/Arabic Language

This was a toy project (result of a “Challenge accepted” type of conversation with one of my friends). It combines the **Kashida** and **White-Space** methods for hiding the message into the cover text. By using this techniques, I was able to hide more message (bits) in less cover text. Note that there is no steganography key, encryption or any kind of security implemented in this tool, it is a toy afterall, just the idea mattered to me. Checkout a live version [here](https://steganography.page.gd/).

TODOs:
1. Message text compression
2. Exploiting the Kashida/Space frequency in the cover text for encoding bits with less changes in the cover text
3. Dynamically choosing bit encoding len based on message characters for encoding even more bits is lesser space

