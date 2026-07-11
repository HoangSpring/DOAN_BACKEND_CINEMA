import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import api from '../lib/api';
import { Badge } from '../components/ui/badge';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '../components/ui/card';
import { Skeleton } from '../components/ui/skeleton';
import { ToggleGroup, ToggleGroupItem } from '../components/ui/toggle-group';
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from '../components/ui/carousel';

interface Movie {
  id: number;
  title: string;
  poster_url: string;
  age_rating: string;
  tags: { id: number; name: string; slug: string }[];
}

interface Tag {
  id: number;
  name: string;
  slug: string;
}

export default function HomePage() {
  const [selectedTags, setSelectedTags] = useState<string[]>([]);

  const { data: tagsData } = useQuery({
    queryKey: ['tags'],
    queryFn: async () => {
      const res = await api.get('/tags');
      return res.data.data as Tag[];
    },
  });

  const { data: moviesData, isLoading } = useQuery({
    queryKey: ['movies', selectedTags],
    queryFn: async () => {
      const params = new URLSearchParams({ status: 'showing' });
      if (selectedTags.length > 0) {
        params.append('tags', selectedTags.join(','));
      }
      const res = await api.get(`/movies?${params.toString()}`);
      return res.data.data as Movie[];
    },
  });

  const featuredMovies = moviesData?.slice(0, 3) || [];

  return (
    <div className="container mx-auto px-4 py-8 space-y-12">
      {/* Featured Banner */}
      <section>
        {isLoading ? (
          <Skeleton className="w-full h-[400px] md:h-[500px] rounded-xl" />
        ) : featuredMovies.length > 0 ? (
          <Carousel className="w-full">
            <CarouselContent>
              {featuredMovies.map((movie) => (
                <CarouselItem key={movie.id}>
                  <div className="relative w-full h-[400px] md:h-[500px] rounded-xl overflow-hidden group">
                    <img
                      src={movie.poster_url || 'https://via.placeholder.com/1200x500'}
                      alt={movie.title}
                      className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent" />
                    <div className="absolute bottom-0 left-0 p-8 w-full md:w-2/3">
                      <div className="flex gap-2 mb-3">
                        <Badge variant="destructive">{movie.age_rating}</Badge>
                        {movie.tags.slice(0, 3).map(tag => (
                          <Badge key={tag.id} variant="secondary">{tag.name}</Badge>
                        ))}
                      </div>
                      <h2 className="text-3xl md:text-5xl font-bold text-white mb-4 shadow-sm">{movie.title}</h2>
                      <Button asChild size="lg" className="rounded-full">
                        <Link to={`/movies/${movie.id}`}>Mua vé ngay</Link>
                      </Button>
                    </div>
                  </div>
                </CarouselItem>
              ))}
            </CarouselContent>
            <CarouselPrevious className="left-4" />
            <CarouselNext className="right-4" />
          </Carousel>
        ) : null}
      </section>

      {/* Movies List */}
      <section>
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
          <h2 className="text-3xl font-bold tracking-tight">Phim Đang Chiếu</h2>
          
          <div className="overflow-x-auto w-full md:w-auto pb-2">
            <ToggleGroup 
              type="multiple" 
              value={selectedTags} 
              onValueChange={setSelectedTags}
              className="justify-start"
            >
              {tagsData?.map(tag => (
                <ToggleGroupItem key={tag.id} value={tag.slug} aria-label={`Toggle ${tag.name}`} className="whitespace-nowrap">
                  {tag.name}
                </ToggleGroupItem>
              ))}
            </ToggleGroup>
          </div>
        </div>

        {isLoading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {Array.from({ length: 8 }).map((_, i) => (
              <Card key={i} className="overflow-hidden border-0 shadow-none">
                <Skeleton className="h-[400px] w-full" />
                <CardHeader className="px-0 pt-4">
                  <Skeleton className="h-6 w-3/4" />
                </CardHeader>
              </Card>
            ))}
          </div>
        ) : moviesData && moviesData.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {moviesData.map((movie) => (
              <Card key={movie.id} className="overflow-hidden group border-0 bg-transparent shadow-none">
                <div className="relative overflow-hidden rounded-xl aspect-[2/3]">
                  <img
                    src={movie.poster_url || 'https://via.placeholder.com/300x450'}
                    alt={movie.title}
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                  />
                  <div className="absolute top-2 left-2 flex gap-1 flex-wrap">
                    <Badge variant="destructive" className="font-semibold">{movie.age_rating}</Badge>
                  </div>
                  <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                    <Button asChild size="lg" className="rounded-full shadow-lg">
                      <Link to={`/movies/${movie.id}`}>Đặt vé</Link>
                    </Button>
                  </div>
                </div>
                <CardHeader className="px-0 pt-4 pb-2">
                  <CardTitle className="text-lg line-clamp-1 group-hover:text-primary transition-colors">{movie.title}</CardTitle>
                </CardHeader>
                <CardContent className="px-0 pb-0">
                  <div className="flex gap-1 flex-wrap">
                    {movie.tags.slice(0, 2).map(tag => (
                      <Badge key={tag.id} variant="outline" className="text-xs text-muted-foreground">{tag.name}</Badge>
                    ))}
                    {movie.tags.length > 2 && (
                      <span className="text-xs text-muted-foreground ml-1">+{movie.tags.length - 2}</span>
                    )}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <div className="text-center py-24 text-muted-foreground bg-muted/20 rounded-xl">
            <p className="text-xl font-medium mb-2">Không tìm thấy phim nào</p>
            <p>Vui lòng thử bỏ chọn một số thể loại lọc.</p>
            {selectedTags.length > 0 && (
              <Button variant="outline" className="mt-4" onClick={() => setSelectedTags([])}>
                Xoá bộ lọc
              </Button>
            )}
          </div>
        )}
      </section>
    </div>
  );
}
