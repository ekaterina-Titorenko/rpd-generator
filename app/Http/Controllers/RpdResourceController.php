<?php

namespace App\Http\Controllers;

use App\Models\RpdProgram;
use App\Models\RpdResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RpdResourceController extends Controller
{
    public function index(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $rpdProgram->load('resources');

        return view('rpd-programs.resources.index', compact('rpdProgram'));
    }

    public function store(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $validated = $this->validateResource($request);

        $metadata = $this->makeMetadata($validated);
        $validated['metadata'] = $metadata;
        $validated['title'] = $this->makeGostTitle($validated['source_type'], $metadata);
        $validated['url'] = $metadata['url'] ?? null;
        $validated['sort_order'] = (int) $rpdProgram->resources()->max('sort_order') + 1;

        $rpdProgram->resources()->create($validated);

        return redirect()
            ->route('rpd-programs.resources.index', $rpdProgram)
            ->with('success', 'Источник добавлен.');
    }

    public function update(Request $request, RpdProgram $rpdProgram, RpdResource $resource)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($resource->rpd_program_id === $rpdProgram->id, 404);

        $validated = $this->validateResource($request);

        $metadata = $this->makeMetadata($validated);
        $validated['metadata'] = $metadata;
        $validated['title'] = $this->makeGostTitle($validated['source_type'], $metadata);
        $validated['url'] = $metadata['url'] ?? null;

        $resource->update($validated);

        return redirect()
            ->route('rpd-programs.resources.index', $rpdProgram)
            ->with('success', 'Источник обновлён.');
    }

    public function destroy(Request $request, RpdProgram $rpdProgram, RpdResource $resource)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($resource->rpd_program_id === $rpdProgram->id, 404);

        $resource->delete();

        return redirect()
            ->route('rpd-programs.resources.index', $rpdProgram)
            ->with('success', 'Источник удалён.');
    }

    private function validateResource(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in([
                'main_recommended',
                'additional',
                'internet',
            ])],
            'source_type' => ['required', Rule::in([
                'book',
                'article',
                'electronic',
                'legal',
            ])],

            'authors' => ['nullable', 'string', 'max:1000'],
            'title' => ['required', 'string', 'max:1000'],
            'publication_place' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:10000'],

            'journal' => ['nullable', 'string', 'max:1000'],
            'issue' => ['nullable', 'string', 'max:255'],
            'article_pages' => ['nullable', 'string', 'max:255'],

            'site_name' => ['nullable', 'string', 'max:1000'],
            'url' => ['nullable', 'string', 'max:1000'],
            'access_date' => ['nullable', 'date'],

            'document_number' => ['nullable', 'string', 'max:255'],
            'document_date' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function makeMetadata(array $data): array
    {
        return [
            'authors' => $data['authors'] ?? null,
            'title' => $data['title'] ?? null,
            'publication_place' => $data['publication_place'] ?? null,
            'publisher' => $data['publisher'] ?? null,
            'year' => $data['year'] ?? null,
            'pages' => $data['pages'] ?? null,
            'journal' => $data['journal'] ?? null,
            'issue' => $data['issue'] ?? null,
            'article_pages' => $data['article_pages'] ?? null,
            'site_name' => $data['site_name'] ?? null,
            'url' => $data['url'] ?? null,
            'access_date' => $data['access_date'] ?? null,
            'document_number' => $data['document_number'] ?? null,
            'document_date' => $data['document_date'] ?? null,
        ];
    }

    private function makeGostTitle(string $sourceType, array $metadata): string
    {
        return match ($sourceType) {
            'book' => $this->makeBookTitle($metadata),
            'article' => $this->makeArticleTitle($metadata),
            'electronic' => $this->makeElectronicTitle($metadata),
            'legal' => $this->makeLegalTitle($metadata),
            default => $metadata['title'],
        };
    }

    private function makeBookTitle(array $metadata): string
    {
        $authors = trim((string) ($metadata['authors'] ?? ''));
        $title = trim((string) ($metadata['title'] ?? ''));
        $place = trim((string) ($metadata['publication_place'] ?? ''));
        $publisher = trim((string) ($metadata['publisher'] ?? ''));
        $year = trim((string) ($metadata['year'] ?? ''));
        $pages = trim((string) ($metadata['pages'] ?? ''));

        $result = $authors ? "{$authors}. {$title}" : $title;

        $publication = collect([$place, $publisher])
            ->filter()
            ->implode(': ');

        if ($publication || $year) {
            $result .= ' — ' . trim($publication . ($publication && $year ? ', ' : '') . $year) . '.';
        }

        if ($pages) {
            $result .= " — {$pages} с.";
        }

        return $result;
    }

    private function makeArticleTitle(array $metadata): string
    {
        $authors = trim((string) ($metadata['authors'] ?? ''));
        $title = trim((string) ($metadata['title'] ?? ''));
        $journal = trim((string) ($metadata['journal'] ?? ''));
        $year = trim((string) ($metadata['year'] ?? ''));
        $issue = trim((string) ($metadata['issue'] ?? ''));
        $articlePages = trim((string) ($metadata['article_pages'] ?? ''));

        $result = $authors ? "{$authors}. {$title}" : $title;

        if ($journal) {
            $result .= " // {$journal}.";
        }

        if ($year) {
            $result .= " — {$year}.";
        }

        if ($issue) {
            $result .= " — № {$issue}.";
        }

        if ($articlePages) {
            $result .= " — С. {$articlePages}.";
        }

        return $result;
    }

    private function makeElectronicTitle(array $metadata): string
    {
        $title = trim((string) ($metadata['title'] ?? ''));
        $siteName = trim((string) ($metadata['site_name'] ?? ''));
        $url = trim((string) ($metadata['url'] ?? ''));
        $accessDate = trim((string) ($metadata['access_date'] ?? ''));

        $result = $title . ' [Электронный ресурс]';

        if ($siteName) {
            $result .= " // {$siteName}.";
        }

        if ($url) {
            $result .= " — URL: {$url}";
        }

        if ($accessDate) {
            $result .= " (дата обращения: {$accessDate}).";
        }

        return $result;
    }

    private function makeLegalTitle(array $metadata): string
    {
        $title = trim((string) ($metadata['title'] ?? ''));
        $number = trim((string) ($metadata['document_number'] ?? ''));
        $date = trim((string) ($metadata['document_date'] ?? ''));

        $result = $title;

        if ($date || $number) {
            $result .= ' от ' . trim($date);
            if ($number) {
                $result .= " № {$number}";
            }
        }

        return $result;
    }

    private function authorizeProgramAccess(Request $request, RpdProgram $rpdProgram): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($rpdProgram->user_id === $request->user()->id, 403);
    }
}