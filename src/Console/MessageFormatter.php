<?php

namespace Tapper\Console;

use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Span;

class MessageFormatter
{
    public static function colorizeInlineJson(string $json): array
    {
        $data = json_decode($json, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return [Span::styled($json, Style::default()->red())];
        }

        return self::encodeInlineSpans($data);
    }

    public static function colorizeFormattedJson(string $json): array
    {
        $data = json_decode($json, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return [Line::fromSpan(Span::styled($json, Style::default()->red()))];
        }

        $lines = [];
        self::encodeFormattedLines($data, 0, $lines);

        return $lines;
    }

    private static function encodeInlineSpans(mixed $data): array
    {
        $spans = [];

        if (is_array($data)) {
            if (array_is_list($data)) {
                $spans[] = Span::fromString('[');
                foreach ($data as $i => $value) {
                    if ($i > 0) {
                        $spans[] = Span::fromString(', ');
                    }
                    $spans = array_merge($spans, self::encodeInlineSpans($value));
                }
                $spans[] = Span::fromString(']');
            } else {
                $spans[] = Span::fromString('{');
                $i = 0;
                foreach ($data as $key => $value) {
                    if ($i++ > 0) {
                        $spans[] = Span::fromString(', ');
                    }

                    $spans[] = Span::fromString('"');
                    $spans[] = Span::styled($key, Style::default()->blue());
                    $spans[] = Span::fromString('": ');
                    $spans = array_merge($spans, self::encodeInlineSpans($value));
                }
                $spans[] = Span::fromString('}');
            }
        } elseif (is_string($data)) {
            $spans[] = Span::fromString('"');
            $spans[] = Span::styled($data, Style::default()->green());
            $spans[] = Span::fromString('"');
        } elseif (is_bool($data)) {
            $spans[] = Span::styled($data ? 'true' : 'false', Style::default()->magenta());
        } elseif (is_numeric($data)) {
            $spans[] = Span::styled((string) $data, Style::default()->cyan());
        } elseif ($data === null) {
            $spans[] = Span::styled('null', Style::default()->gray());
        }

        return $spans;
    }

    private static function encodeFormattedLines(mixed $data, int $indent, array &$lines): void
    {
        $pad = str_repeat('  ', $indent);

        if (is_array($data)) {
            $isList = array_is_list($data);
            $lines[] = Line::fromSpan(Span::fromString($pad.($isList ? '[' : '{')));

            $i = 0;
            foreach ($data as $key => $value) {
                $lineSpans = [Span::fromString($pad.'  ')];

                if (! $isList) {
                    $lineSpans[] = Span::fromString('"');
                    $lineSpans[] = Span::styled($key, Style::default()->blue());
                    $lineSpans[] = Span::fromString('": ');
                }

                if (is_array($value)) {
                    $lines[] = new Line($lineSpans);
                    self::encodeFormattedLines($value, $indent + 1, $lines);
                } else {
                    $lineSpans = array_merge($lineSpans, self::encodeInlineSpans($value));
                    $lines[] = new Line($lineSpans);
                }

                $i++;
            }

            $lines[] = Line::fromSpan(Span::fromString($pad.($isList ? ']' : '}')));
        } else {
            $lines[] = new Line(self::encodeInlineSpans($data));
        }
    }
}
